import { Inject, Injectable } from '@nestjs/common';
import { UploadIntent } from '../../domain/UploadIntent';
import { UPLOAD_INTENT_REPOSITORY } from '../../domain/UploadIntentRepository';
import type { UploadIntentRepository } from '../../domain/UploadIntentRepository';
import { Type } from 'class-transformer';
import {
  ArrayMaxSize,
  ArrayNotEmpty,
  IsArray,
  IsIn,
  IsInt,
  IsNotEmpty,
  IsOptional,
  IsPositive,
  IsString,
  IsUUID,
  Matches,
  Max,
  ValidateNested,
} from 'class-validator';

const ACCEPTED_FORMATS = ['webp', 'jpeg', 'png'] as const;
const MAX_SIZE_BYTES_CEILING = 50 * 1024 * 1024;
const MAX_VARIANTS = 10;

export class VariantSpecDto {
  @IsString()
  @IsNotEmpty()
  name: string; // "thumbnail", "medium", ...

  @IsString()
  @IsIn(ACCEPTED_FORMATS)
  format: string;

  @IsOptional()
  @IsInt()
  @IsPositive()
  width?: number;

  @IsOptional()
  @IsInt()
  @IsPositive()
  height?: number;
}

export class UploadConstraintsDto {
  @IsArray()
  @ArrayNotEmpty()
  @IsIn(ACCEPTED_FORMATS, { each: true })
  formats: string[];

  @IsInt()
  @IsPositive()
  @Max(MAX_SIZE_BYTES_CEILING)
  maxSizeBytes: number;

  @IsArray()
  @ArrayNotEmpty()
  @ArrayMaxSize(MAX_VARIANTS)
  @ValidateNested({ each: true })
  @Type(() => VariantSpecDto)
  variants: VariantSpecDto[];
}

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
  @ValidateNested()
  @Type(() => UploadConstraintsDto)
  constraints: UploadConstraintsDto;
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
  ) {}

  async execute(command: CreateUploadIntentCommand): Promise<UploadIntent> {
    const uploadIntent = UploadIntent.create({
      entityGid: command.entityGid,
      routingKey: command.routingKey,
      constraints: command.constraints,
      userId: command.userId,
      ttlSeconds: command.ttlSeconds,
    });

    await this.uploadIntents.save(uploadIntent);
    return uploadIntent;
  }
}
