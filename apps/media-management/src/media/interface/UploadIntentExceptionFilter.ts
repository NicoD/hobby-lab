import {
  ArgumentsHost,
  Catch,
  ExceptionFilter,
  HttpStatus,
} from '@nestjs/common';
import type { Response } from 'express';
import {
  UploadIntentAlreadyConsumedError,
  UploadIntentExpiredError,
  UploadIntentNotFoundError,
  UploadIntentWrongUserError,
} from '../domain/UploadIntentErrors';

type UploadIntentError =
  | UploadIntentNotFoundError
  | UploadIntentWrongUserError
  | UploadIntentAlreadyConsumedError
  | UploadIntentExpiredError;

@Catch(
  UploadIntentNotFoundError,
  UploadIntentWrongUserError,
  UploadIntentAlreadyConsumedError,
  UploadIntentExpiredError,
)
export class UploadIntentExceptionFilter implements ExceptionFilter<UploadIntentError> {
  catch(exception: UploadIntentError, host: ArgumentsHost): void {
    const response = host.switchToHttp().getResponse<Response>();
    response
      .status(this.statusFor(exception))
      .json({ message: exception.message });
  }

  private statusFor(exception: UploadIntentError): HttpStatus {
    if (exception instanceof UploadIntentNotFoundError) {
      return HttpStatus.NOT_FOUND;
    }
    if (exception instanceof UploadIntentWrongUserError) {
      return HttpStatus.FORBIDDEN;
    }
    if (exception instanceof UploadIntentAlreadyConsumedError) {
      return HttpStatus.CONFLICT;
    }
    return HttpStatus.GONE;
  }
}
