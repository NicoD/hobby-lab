export class UploadIntentNotFoundError extends Error {
  constructor() {
    super(`Upload intent not found for token`);
  }
}

export class UploadIntentWrongUserError extends Error {
  constructor() {
    super('Upload intent does not belong to this user');
  }
}

export class UploadIntentAlreadyConsumedError extends Error {
  constructor() {
    super('Upload intent has already been consumed');
  }
}

export class UploadIntentExpiredError extends Error {
  constructor() {
    super('Upload intent has expired');
  }
}
