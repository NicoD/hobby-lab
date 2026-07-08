import { Injectable } from '@nestjs/common';
import { Prisma } from '../../../generated/prisma/client';
import { PrismaService } from './PrismaService';
import { UploadIntentRepository } from '../domain/UploadIntentRepository';
import { UploadIntent } from '../domain/UploadIntent';

@Injectable()
export class UploadIntentPrismaRepository implements UploadIntentRepository {
  constructor(private readonly prisma: PrismaService) {}
  async save(uploadIntent: UploadIntent): Promise<void> {
    await this.prisma.uploadIntent.create({
      data: {
        id: uploadIntent.id,
        token: uploadIntent.token,
        entityGid: uploadIntent.entityGid,
        userId: uploadIntent.userId,
        routingKey: uploadIntent.routingKey,
        constraints:
          uploadIntent.constraints as unknown as Prisma.InputJsonValue,
        expiresAt: uploadIntent.expiresAt,
        createdAt: uploadIntent.createdAt,
      },
    });
  }
}
