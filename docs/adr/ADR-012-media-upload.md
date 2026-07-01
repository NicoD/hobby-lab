# ADR-012 — Media upload architecture

**Status**: Accepted

## Context

Several domains (ColorLab catalog, Stash, etc.) need to associate images with their entities (brands, paints, stash items). Each domain has different constraints: accepted formats, maximum size, and expected variants.

Three upload approaches were evaluated:

1. **Proxy (synchronous)** — the domain endpoint receives the file, forwards it to the Media service, gets back a `mediaId` synchronously.
2. **Proxy (asynchronous)** — same as above, but the Media service emits a Domain Event; the consuming domain listens and updates asynchronously.
3. **Signed Upload URL** — the consuming domain pre-authorizes the upload (constraints, TTL); the client uploads the file directly to the Media service via a single-use signed URL; the Media service emits an event.

Option 3 is chosen. The file never transits through the consuming domain's backend. Constraints are encoded upfront in the signed token. Orphaned media is structurally impossible (the upload URL is tied to an entity from the start).

This is not the classical "presigned URL to external storage" (S3 presigned URL) variant: the signed URL points to the `media-manager` service itself, not to an external storage provider. This preserves the decoupling benefits of Option 3 while keeping full control over image processing.

## Decisions

### 1. Dedicated `media-manager` service

A new application `apps/media-manager` is created (NestJS). It is the single owner of all media: storage, transformation, and serving.

No domain ever touches a file directly. All file operations go through `media-manager`.

### 2. Upload flow

```
(1) Consumer domain  →  POST /api/media/generate-upload-url
                         body: { formats, maxSizeBytes, variants, routingKey }
                      ←  { uploadUrl: "/api/media/upload/<signed-token>" }

(2) Consumer domain  ←  returns { uploadUrl, ... } to the client

(3) Client           →  PUT /api/media/upload/<signed-token>  (binary file)
                            media-manager validates token (constraints, TTL, single-use)
                            Sharp generates all requested variants + stores original
                            All files written to MinIO
                            DB record created: { mediaId, storageKeys }
                            MediaUploaded published to RabbitMQ

(4) Consumer domain  ←  listens to its MediaUploaded routing key
                            updates entity: mediaId = <mediaId>
                            pushes WebSocket message to the client: { invalidate: [queryKey] }

(5) Client           ←  React Query sees invalidation, refetches the entity
```

The signed token encodes: `{ entityId, entityType, formats, maxSizeBytes, variants, routingKey, expiresAt }`. It is signed with a secret known only to `media-manager`. It is single-use: consumed atomically on upload.

### 3. Image transformation at write time

Sharp runs once at upload. It produces:
- The **original** (stored untouched — permanent source of truth)
- All **variants** declared in the token (`thumbnail`, `medium`, etc.)

All files are written to MinIO. `media-manager` stores no files on its own disk.

Transformation at read time (on-the-fly proxy) is explicitly rejected: it adds runtime overhead for every request and requires a proxy layer. The original is always available for batch regeneration if new variants are needed later.

### 4. Storage — MinIO (S3-compatible)

MinIO is used as the physical file store. It is S3-compatible: swapping to AWS S3 in production requires only a configuration change, no code change.

`media-manager` stores a **storage key** per file in its database (e.g., `<mediaId>/thumbnail.webp`), never a full URL.

### 5. URL template — consumer assembles URLs

`media-manager` exposes a single endpoint:

```
GET /api/media/url-template
← { template: "http://media-manager/api/media/{mediaId}/{variant}.{format}" }
```

Consuming services fetch this template at startup and cache it indefinitely. The template is treated as **immutable**: it can only change as part of a planned infrastructure migration with a coordinated deployment. No invalidation mechanism is implemented.

Each domain assembles the final URL when serializing a response to the client:

```
template
  .replace('{mediaId}', entity.mediaId)
  .replace('{variant}', 'thumbnail')
  .replace('{format}', 'webp')
```

Domains store only the `mediaId` in their entities, never a full URL.

### 6. Event routing — Return Address pattern

`media-manager` publishes `MediaUploaded` to the existing `domain.events` Topic Exchange (ADR-007).

The routing key is **not chosen by `media-manager`** — it is supplied by the caller as the `routingKey` field in the upload intent request. `media-manager` publishes on exactly that key, without modification.

This is the **Return Address** pattern (Enterprise Integration Patterns): the requester embeds a callback address in its request; the processor notifies on that address when the work is done. It is the messaging equivalent of webhooks.

```
Catalog requests intent with routingKey: "colorlab.brand.media-uploaded"
  → media-manager stores routingKey in the signed token
  → on upload complete, publishes MediaUploaded on "colorlab.brand.media-uploaded"
  → only Catalog's queue, bound to "colorlab.brand.media-uploaded", receives it
```

This routing key does not follow the ADR-007 `{domain}.{entity}.{action}` convention intentionally: it is not a business event describing what happened inside `media-manager`. It is a callback address controlled by the consumer. The consumer chooses a key that fits its own binding strategy.

Each consuming domain binds its queue to its own routing key. It never receives uploads intended for other domains.

### 7. WebSocket — client cache invalidation

WebSocket is not a general replacement for HTTP invalidation. It is used **only when the result of an operation cannot be returned in the HTTP response** — i.e., when the outcome arrives from a separate async channel.

In this architecture, the only case is `MediaUploaded`: the client uploaded a file directly to `media-manager` and has no HTTP response carrying the final `mediaId`. All other mutations (entity creation, updates, deletion) remain synchronous: the HTTP response is the confirmation, and React Query invalidation happens normally after the mutation.

#### Service — `apps/websocket-bridge`

WebSocket connections are managed by a dedicated service `apps/websocket-bridge` (NestJS). It has no domain logic and no database — it is pure infrastructure: RabbitMQ in, WebSocket out.

It is isolated from `apps/user` intentionally: the identity service is the PII boundary and must not accumulate unrelated responsibilities.

#### Event chain

```
media-manager     →  MediaUploaded        (RabbitMQ — Return Address routing key)
apps/backend      →  listens, updates Brand.mediaId
                  →  BrandMediaUpdated    (RabbitMQ)
apps/websocket-bridge     →  listens BrandMediaUpdated
                  →  pushes semantic event to the authenticated client via WebSocket
```

#### Semantic events

The server sends what happened in the domain — not cache instructions. The client owns the mapping from domain event to cache invalidation:

```json
{ "type": "brand.media-uploaded", "payload": { "brandId": "123" } }
```

```typescript
ws.onmessage = (event) => {
  const { type, payload } = JSON.parse(event.data)
  if (type === "brand.media-uploaded") {
    queryClient.invalidateQueries({ queryKey: ["brands", payload.brandId] })
  }
}
```

This decouples the server from the client's cache structure. If React Query keys change on the frontend, the backend is unaffected.

#### Horizontal scaling — Redis Pub/Sub

When multiple instances of `apps/websocket-bridge` run concurrently, a WebSocket connection for a given user may be held by any instance. An event arriving on instance 2 cannot reach a client connected to instance 1.

Redis Pub/Sub solves this: every instance subscribes to a shared Redis channel (`ws-events`). When any instance receives a RabbitMQ event, it publishes to Redis. All instances receive the broadcast; only the one holding the target `userId` connection pushes to the WebSocket.

```
Instance 2 receives BrandMediaUpdated
  → PUBLISH redis:"ws-events" { userId, type, payload }

Instance 1  → receives → userId not connected → ignores
Instance 2  → receives → userId connected     → pushes WS
Instance 3  → receives → userId not connected → ignores
```

Redis Pub/Sub does not persist messages. If a message is published while no instance holds the target connection, it is lost. This is acceptable: the client reconnects and React Query refetches. The source of truth is the database, not the WebSocket channel.

### 8. Upload forms — client-side separation

Upload is always a separate concern from the entity form submission. A form that includes an image goes through two independent steps:

1. Entity created/updated via its domain endpoint (name, metadata, etc.)
2. Upload URL requested via `POST /<domain>/<entity>/<id>/upload-url`
3. File uploaded via `PUT <uploadUrl>`

The client orchestrates these steps. From the user's perspective it is a single action; from the backend's perspective the entity always exists before its media.

#### UX — processing state

Optimistic UI is explicitly rejected for media: the client does not have the final processed URL, and overriding the React Query cache while waiting for the WebSocket event would require local state management that conflicts with the cache lifecycle.

Instead, the form displays a **spinner** from the moment the file is sent until the WebSocket event is received:

```
PUT uploadUrl     →  spinner starts
WebSocket event   →  spinner stops, React Query invalidates, photo appears
```

This is honest UX: the user knows processing is in progress. The latency exists but is communicated, which makes it acceptable.

Other parts of the UI that display the same media (outside the form) update naturally when React Query refetches their data — either because they share the same query key and are invalidated at the same time, or at their next mount/focus. No additional coordination is needed.


## Consequences

- The consuming domain never handles file bytes. Its upload endpoint is limited to token generation and returning the upload URL.
- Media orphans are structurally impossible: the upload token is bound to an existing entity at creation time.
- Domain-specific constraints (formats, size, variants) are encoded per upload token, not centralized in `media-manager`.
- Adding a new variant format requires: generating it from stored originals (batch job) and updating the URL template if the naming convention changes.
- The `routingKey` is a caller-supplied Return Address. `media-manager` does not validate that the caller is authorized to publish on that key.
- URL migration (CDN change, domain rename) requires a data migration on all entities storing `mediaId`. The URL template being immutable means this is a planned, coordinated event — not an emergency.
- The WebSocket connection lifetime and reconnection strategy are outside the scope of this ADR and left to each consuming domain's implementation.
