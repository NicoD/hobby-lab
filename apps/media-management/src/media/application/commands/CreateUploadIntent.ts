import { Inject, Injectable } from '@nestjs/common';
import { UploadIntent } from '../../domain/UploadIntent';
import { UPLOAD_INTENT_REPOSITORY } from '../../domain/UploadIntentRepository';
import type { UploadIntentRepository } from '../../domain/UploadIntentRepository';
import { VARIANT_FORMAT_CATALOG } from '../../domain/VariantFormatCatalog';
import type { VariantFormatCatalog } from '../../domain/VariantFormatCatalog';
import {
  ArrayMaxSize,
  ArrayNotEmpty,
  IsArray,
  IsInt,
  IsNotEmpty,
  IsPositive,
  IsString,
  IsUUID,
  Matches,
  Max,
} from 'class-validator';

const MAX_VARIANTS = 10;

export class CreateUploadIntentCommand {
  @IsString()
  @Matches(/^gid:\/\/[a-z0-9-]+\/[A-Za-z]+\/[A-Za-z0-9-]+$/)
  entityGid: string;
  @IsString()
  @IsNotEmpty()
  @Matches(/^[a-z0-9]+(\.[a-z0-9-]+)*$/)
  routingKey: string;
  @IsUUID()
  userId: string;
  @IsArray()
  @ArrayNotEmpty()
  @ArrayMaxSize(MAX_VARIANTS)
  @IsString({ each: true })
  @IsNotEmpty({ each: true })
  variants: string[]; // names — must exist in the VariantFormatCatalog
  @IsInt()
  @IsPositive()
  @Max(3600)
  ttlSeconds: number;
}

@Injectable()
export class CreateUploadIntentHandler {
  constructor(
    @Inject(UPLOAD_INTENT_REPOSITORY)
    private readonly uploadIntents: UploadIntentRepository,
    @Inject(VARIANT_FORMAT_CATALOG)
    private readonly variantFormats: VariantFormatCatalog,
  ) {}

  async execute(command: CreateUploadIntentCommand): Promise<UploadIntent> {
    command.variants.forEach((name) => this.variantFormats.resolve(name));

    const uploadIntent = UploadIntent.create({
      entityGid: command.entityGid,
      routingKey: command.routingKey,
      variants: command.variants,
      userId: command.userId,
      ttlSeconds: command.ttlSeconds,
    });

    await this.uploadIntents.create(uploadIntent);
    return uploadIntent;
  }
}
