import { randomUUID } from 'crypto';
import {
  UploadIntentAlreadyConsumedError,
  UploadIntentExpiredError,
  UploadIntentWrongUserError,
} from './UploadIntentErrors';

export class UploadIntent {
  private constructor(
    public readonly id: string,
    public readonly token: string,
    public readonly entityGid: string,
    public readonly userId: string,
    public readonly routingKey: string,
    public readonly variants: string[], // names — resolved to format/dimensions by VariantFormatCatalog
    public readonly expiresAt: Date,
    private consumedAt: Date | null,
    public readonly createdAt: Date,
  ) {}

  consume(): Date {
    const consumedAt = new Date();
    this.consumedAt = consumedAt;
    return consumedAt;
  }

  assertConsumable(userId: string): void {
    if (userId !== this.userId) {
      throw new UploadIntentWrongUserError();
    }
    if (this.consumedAt !== null) {
      throw new UploadIntentAlreadyConsumedError();
    }
    if (this.expiresAt < new Date()) {
      throw new UploadIntentExpiredError();
    }
  }

  static create(params: {
    entityGid: string;
    userId: string;
    routingKey: string;
    variants: string[];
    ttlSeconds: number;
  }): UploadIntent {
    return new UploadIntent(
      randomUUID(),
      randomUUID(),
      params.entityGid,
      params.userId,
      params.routingKey,
      params.variants,
      new Date(Date.now() + params.ttlSeconds * 1000),
      null,
      new Date(),
    );
  }

  static reconstitute(params: {
    id: string;
    token: string;
    entityGid: string;
    userId: string;
    routingKey: string;
    variants: string[];
    expiresAt: Date;
    consumedAt: Date | null;
    createdAt: Date;
  }): UploadIntent {
    return new UploadIntent(
      params.id,
      params.token,
      params.entityGid,
      params.userId,
      params.routingKey,
      params.variants,
      params.expiresAt,
      params.consumedAt,
      params.createdAt,
    );
  }
}
