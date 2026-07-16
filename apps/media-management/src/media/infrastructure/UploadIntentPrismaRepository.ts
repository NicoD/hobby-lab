import { Injectable } from '@nestjs/common';
import {
  Prisma,
  UploadIntent as UploadIntentRow,
} from '../../../generated/prisma/client';
import { PrismaService } from './PrismaService';
import { UploadIntentRepository } from '../domain/UploadIntentRepository';
import { UploadIntent } from '../domain/UploadIntent';
import type { TransactionContext } from '../domain/UnitOfWork';

@Injectable()
export class UploadIntentPrismaRepository implements UploadIntentRepository {
  constructor(private readonly prisma: PrismaService) {}

  private client(tx?: TransactionContext) {
    return (tx as Prisma.TransactionClient | undefined) ?? this.prisma;
  }

  async create(uploadIntent: UploadIntent): Promise<void> {
    await this.prisma.uploadIntent.create({
      data: {
        id: uploadIntent.id,
        token: uploadIntent.token,
        entityGid: uploadIntent.entityGid,
        userId: uploadIntent.userId,
        routingKey: uploadIntent.routingKey,
        variants: uploadIntent.variants,
        expiresAt: uploadIntent.expiresAt,
        createdAt: uploadIntent.createdAt,
      },
    });
  }

  async findByToken(token: string): Promise<UploadIntent | null> {
    const row = await this.prisma.uploadIntent.findUnique({
      where: { token },
    });
    return row ? this.toDomain(row) : null;
  }

  async markConsumed(
    token: string,
    userId: string,
    consumedAt: Date,
    tx?: TransactionContext,
  ): Promise<boolean> {
    const { count } = await this.client(tx).uploadIntent.updateMany({
      where: {
        token,
        userId,
        consumedAt: null,
        expiresAt: { gt: consumedAt },
      },
      data: { consumedAt },
    });
    return count > 0;
  }

  private toDomain(row: UploadIntentRow): UploadIntent {
    return UploadIntent.reconstitute({
      id: row.id,
      token: row.token,
      entityGid: row.entityGid,
      userId: row.userId,
      routingKey: row.routingKey,
      variants: row.variants,
      expiresAt: row.expiresAt,
      consumedAt: row.consumedAt,
      createdAt: row.createdAt,
    });
  }
}
