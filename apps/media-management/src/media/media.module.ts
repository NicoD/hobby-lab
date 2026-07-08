import { Module } from '@nestjs/common';
import { CreateUploadIntentHandler } from './application/commands/CreateUploadIntent';
import { UploadIntentController } from './interface/UploadIntentController';
import { PrismaService } from './infrastructure/PrismaService';
import { UPLOAD_INTENT_REPOSITORY } from './domain/UploadIntentRepository';
import { UploadIntentPrismaRepository } from './infrastructure/UploadIntentPrismaRepository';

@Module({
  controllers: [UploadIntentController],
  providers: [
    CreateUploadIntentHandler,
    PrismaService,
    {
      provide: UPLOAD_INTENT_REPOSITORY,
      useClass: UploadIntentPrismaRepository,
    },
  ],
  exports: [],
})
export class MediaModule {}
