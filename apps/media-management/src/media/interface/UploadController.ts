import {
  Controller,
  Headers,
  HttpCode,
  HttpStatus,
  Param,
  Post,
  UploadedFile,
  UseFilters,
  UseInterceptors,
} from '@nestjs/common';
import { UploadCommand, UploadHandler } from '../application/commands/Upload';
import { UploadIntentExceptionFilter } from './UploadIntentExceptionFilter';
import { MulterExceptionFilter } from './MulterExceptionFilter';
import { plainToInstance } from 'class-transformer';
import { validateOrReject } from 'class-validator';
import { FileInterceptor } from '@nestjs/platform-express';

const MAX_UPLOAD_SIZE_BYTES = Number(
  process.env.MEDIA_MAX_UPLOAD_SIZE_BYTES ?? 50 * 1024 * 1024,
);

@Controller('media/upload')
@UseFilters(UploadIntentExceptionFilter, MulterExceptionFilter)
export class UploadController {
  constructor(private readonly uploadHandler: UploadHandler) {}

  @Post(':token')
  @UseInterceptors(
    FileInterceptor('file', { limits: { fileSize: MAX_UPLOAD_SIZE_BYTES } }),
  )
  @HttpCode(HttpStatus.OK)
  async upload(
    @Param('token') token: string,
    @Headers() headers: Record<string, string>,
    @UploadedFile() file: Express.Multer.File,
  ): Promise<void> {
    const command = plainToInstance(UploadCommand, {
      userId: headers['X-User-Id'] ?? '',
      token,
      file,
    });
    await validateOrReject(command);

    await this.uploadHandler.execute(command);
    return Promise.resolve();
  }
}
