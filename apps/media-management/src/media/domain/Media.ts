import { randomUUID } from 'crypto';

export interface MediaVariant {
  name: string;
  format: string;
  storageKey: string;
  width: number;
  height: number;
  uploadIntentId: string;
}

export class Media {
  private constructor(
    readonly id: string,
    readonly originalStorageKey: string,
    readonly originalMimeType: string,
    readonly originalSize: number,
    readonly variants: MediaVariant[],
    readonly createdAt: Date,
  ) {}

  static create(params: {
    originalStorageKey: string;
    originalMimeType: string;
    originalSize: number;
    variants: MediaVariant[];
  }): Media {
    return new Media(
      randomUUID(),
      params.originalStorageKey,
      params.originalMimeType,
      params.originalSize,
      params.variants,
      new Date(),
    );
  }

  addVariant(variant: MediaVariant): void {
    const alreadyExists = this.variants.some(
      (existing) => existing.name === variant.name,
    );
    if (alreadyExists) {
      return;
    }
    this.variants.push(variant);
  }

  static reconstitute(params: {
    id: string;
    originalStorageKey: string;
    originalMimeType: string;
    originalSize: number;
    variants: MediaVariant[];
    createdAt: Date;
  }): Media {
    return new Media(
      params.id,
      params.originalStorageKey,
      params.originalMimeType,
      params.originalSize,
      params.variants,
      params.createdAt,
    );
  }
}
