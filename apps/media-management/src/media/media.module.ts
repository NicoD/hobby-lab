import { Module } from '@nestjs/common';
import { CreateUploadIntentHandler } from './application/commands/CreateUploadIntent';
import { UploadHandler } from './application/commands/Upload';
import { UploadIntentController } from './interface/UploadIntentController';
import { UploadController } from './interface/UploadController';
import { PrismaService } from './infrastructure/PrismaService';
import { UPLOAD_INTENT_REPOSITORY } from './domain/UploadIntentRepository';
import { UploadIntentPrismaRepository } from './infrastructure/UploadIntentPrismaRepository';
import { MEDIA_REPOSITORY } from './domain/MediaRepository';
import { MediaPrismaRepository } from './infrastructure/MediaPrismaRepository';
import { VARIANT_FORMAT_CATALOG } from './domain/VariantFormatCatalog';
import { ConfigVariantFormatCatalog } from './infrastructure/ConfigVariantFormatCatalog';
import { UNIT_OF_WORK } from './domain/UnitOfWork';
import { PrismaUnitOfWork } from './infrastructure/PrismaUnitOfWork';

@Module({
  controllers: [UploadIntentController, UploadController],
  providers: [
    CreateUploadIntentHandler,
    UploadHandler,
    PrismaService,
    {
      provide: UPLOAD_INTENT_REPOSITORY,
      useClass: UploadIntentPrismaRepository,
    },
    {
      provide: MEDIA_REPOSITORY,
      useClass: MediaPrismaRepository,
    },
    {
      provide: VARIANT_FORMAT_CATALOG,
      useClass: ConfigVariantFormatCatalog,
    },
    {
      provide: UNIT_OF_WORK,
      useClass: PrismaUnitOfWork,
    },
  ],
  exports: [],
})
export class MediaModule {}
