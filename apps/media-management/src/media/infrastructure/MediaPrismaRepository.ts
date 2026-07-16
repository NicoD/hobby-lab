import { Injectable } from '@nestjs/common';
import { Prisma, Media as MediaRow } from '../../../generated/prisma/client';
import { PrismaService } from './PrismaService';
import { MediaRepository } from '../domain/MediaRepository';
import { Media, MediaVariant } from '../domain/Media';
import type { TransactionContext } from '../domain/UnitOfWork';

@Injectable()
export class MediaPrismaRepository implements MediaRepository {
  constructor(private readonly prisma: PrismaService) {}

  private client(tx?: TransactionContext) {
    return (tx as Prisma.TransactionClient | undefined) ?? this.prisma;
  }

  async create(media: Media, tx?: TransactionContext): Promise<void> {
    await this.client(tx).media.create({
      data: {
        id: media.id,
        originalStorageKey: media.originalStorageKey,
        originalMimeType: media.originalMimeType,
        originalSize: media.originalSize,
        variants: media.variants as unknown as Prisma.InputJsonValue,
        createdAt: media.createdAt,
      },
    });
  }

  async update(media: Media, tx?: TransactionContext): Promise<void> {
    await this.client(tx).media.update({
      where: { id: media.id },
      data: {
        variants: media.variants as unknown as Prisma.InputJsonValue,
      },
    });
  }

  async findByOriginalStorageKey(storageKey: string): Promise<Media | null> {
    const row = await this.prisma.media.findUnique({
      where: { originalStorageKey: storageKey },
    });
    return row ? this.toDomain(row) : null;
  }

  private toDomain(row: MediaRow): Media {
    return Media.reconstitute({
      id: row.id,
      originalStorageKey: row.originalStorageKey,
      originalMimeType: row.originalMimeType,
      originalSize: row.originalSize,
      variants: row.variants as unknown as MediaVariant[],
      createdAt: row.createdAt,
    });
  }
}
