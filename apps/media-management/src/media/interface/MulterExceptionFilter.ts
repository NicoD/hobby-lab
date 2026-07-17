import {
  ArgumentsHost,
  Catch,
  ExceptionFilter,
  HttpStatus,
} from '@nestjs/common';
import type { Response } from 'express';
import { MulterError } from 'multer';

@Catch(MulterError)
export class MulterExceptionFilter implements ExceptionFilter<MulterError> {
  catch(exception: MulterError, host: ArgumentsHost): void {
    const response = host.switchToHttp().getResponse<Response>();
    response
      .status(this.statusFor(exception))
      .json({ message: exception.message });
  }

  private statusFor(exception: MulterError): HttpStatus {
    if (exception.code === 'LIMIT_FILE_SIZE') {
      return HttpStatus.PAYLOAD_TOO_LARGE;
    }
    return HttpStatus.BAD_REQUEST;
  }
}
