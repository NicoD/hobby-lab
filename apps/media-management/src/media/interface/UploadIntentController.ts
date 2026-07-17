import {
  Body,
  Controller,
  HttpCode,
  HttpStatus,
  Post,
  UseFilters,
} from '@nestjs/common';
import {
  CreateUploadIntentCommand,
  CreateUploadIntentHandler,
} from '../application/commands/CreateUploadIntent';
import { MediaExceptionFilter } from './MediaExceptionFilter';

@Controller()
@UseFilters(MediaExceptionFilter)
export class UploadIntentController {
  constructor(
    private readonly createUploadIntentHandler: CreateUploadIntentHandler,
  ) {}

  @Post('/internal/media/generate-upload-url')
  @HttpCode(HttpStatus.OK)
  async generateUploadUrl(
    @Body() command: CreateUploadIntentCommand,
  ): Promise<{ uploadUrl: string }> {
    const uploadIntent = await this.createUploadIntentHandler.execute(command);
    return { uploadUrl: `/api/media/upload/${uploadIntent.token}` };
  }
}
