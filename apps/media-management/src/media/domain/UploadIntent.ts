import { randomUUID } from 'crypto';

export interface UploadConstraints {
  formats: string[]; // mime types / extensions acceptés
  maxSizeBytes: number;
  variants: VariantSpec[];
}

export interface VariantSpec {
  name: string; // "thumbnail", "medium", ...
  format: string; // "webp", "jpeg", ...
  width?: number;
  height?: number;
}

// --- Entité domaine ---
export class UploadIntent {
  private constructor(
    readonly id: string,
    readonly token: string,
    readonly entityGid: string,
    readonly userId: string,
    readonly routingKey: string,
    readonly constraints: UploadConstraints,
    readonly expiresAt: Date,
    readonly consumedAt: Date | null,
    readonly createdAt: Date,
  ) {}

  static create(params: {
    entityGid: string;
    userId: string;
    routingKey: string;
    constraints: UploadConstraints;
    ttlSeconds: number;
  }): UploadIntent {
    return new UploadIntent(
      randomUUID(),
      randomUUID(),
      params.entityGid,
      params.userId,
      params.routingKey,
      params.constraints,
      new Date(Date.now() + params.ttlSeconds * 1000),
      null,
      new Date(),
    );
  }
}
