import { Inject, Injectable } from '@nestjs/common';
import { UPLOAD_INTENT_REPOSITORY } from '../../domain/UploadIntentRepository';
import type { UploadIntentRepository } from '../../domain/UploadIntentRepository';
import { MEDIA_REPOSITORY } from '../../domain/MediaRepository';
import type { MediaRepository } from '../../domain/MediaRepository';
import { VARIANT_FORMAT_CATALOG } from '../../domain/VariantFormatCatalog';
import type { VariantFormatCatalog } from '../../domain/VariantFormatCatalog';
import { UNIT_OF_WORK } from '../../domain/UnitOfWork';
import type { UnitOfWork } from '../../domain/UnitOfWork';
import { Media } from '../../domain/Media';
import {
  UploadIntentAlreadyConsumedError,
  UploadIntentNotFoundError,
} from '../../domain/UploadIntentErrors';
import { IsNotEmpty, IsString, IsUUID } from 'class-validator';
import { Readable } from 'stream';

export class UploadCommand {
  @IsUUID()
  userId: string;

  @IsString()
  @IsNotEmpty()
  token: string;

  @IsNotEmpty()
  file: UploadedFile;
}

// @see Express.Multer.File
export type UploadedFile = {
  fieldname: string;
  originalname: string;
  encoding: string;
  mimetype: string;
  size: number;
  stream: Readable;
  destination: string;
  filename: string;
  path: string;
  buffer: Buffer;
};

@Injectable()
export class UploadHandler {
  constructor(
    @Inject(UPLOAD_INTENT_REPOSITORY)
    private readonly uploadIntents: UploadIntentRepository,
    @Inject(MEDIA_REPOSITORY)
    private readonly medias: MediaRepository,
    @Inject(VARIANT_FORMAT_CATALOG)
    private readonly variantFormats: VariantFormatCatalog,
    @Inject(UNIT_OF_WORK)
    private readonly unitOfWork: UnitOfWork,
  ) {}

  async execute(command: UploadCommand): Promise<void> {
    const uploadIntent = await this.uploadIntents.findByToken(command.token);

    if (!uploadIntent) {
      throw new UploadIntentNotFoundError();
    }

    uploadIntent.assertConsumable(command.userId);

    // TODO: store command.file on MinIO (original + Sharp-generated variants)
    const originalMimeType = '';
    const originalStorageKey = '';
    const originalSize = 0;
    const variants = uploadIntent.variants.flatMap((name) => {
      const variantFormat = this.variantFormats.resolve(name);
      return variantFormat.densities.map((density) => ({
        name: density === 1 ? name : `${name}@${density}x`,
        format: variantFormat.format,
        storageKey: '',
        width: variantFormat.width * density,
        height: variantFormat.height * density,
        uploadIntentId: uploadIntent.id,
      }));
    });

    const consumedAt = uploadIntent.consume();

    await this.unitOfWork.run(async (tx) => {
      const consumed = await this.uploadIntents.markConsumed(
        command.token,
        command.userId,
        consumedAt,
        tx,
      );
      if (!consumed) {
        throw new UploadIntentAlreadyConsumedError();
      }

      const existingMedia =
        await this.medias.findByOriginalStorageKey(originalStorageKey);

      if (existingMedia === null) {
        const media = Media.create({
          originalStorageKey,
          originalMimeType,
          originalSize,
          variants,
        });
        await this.medias.create(media, tx);
      } else {
        variants.forEach((variant) => existingMedia.addVariant(variant));
        await this.medias.update(existingMedia, tx);
      }
    });

    // TODO: publish MediaUploaded on uploadIntent.routingKey
  }
}
