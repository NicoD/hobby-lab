import {
  ArgumentsHost,
  Catch,
  ExceptionFilter,
  HttpStatus,
} from '@nestjs/common';
import type { Response } from 'express';
import { UnknownVariantFormatError } from '../domain/MediaErrors';

@Catch(UnknownVariantFormatError)
export class MediaExceptionFilter implements ExceptionFilter<UnknownVariantFormatError> {
  catch(exception: UnknownVariantFormatError, host: ArgumentsHost): void {
    const response = host.switchToHttp().getResponse<Response>();
    response
      .status(HttpStatus.BAD_REQUEST)
      .json({ message: exception.message });
  }
}
