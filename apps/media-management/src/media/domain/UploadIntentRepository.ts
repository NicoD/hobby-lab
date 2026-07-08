import { UploadIntent } from './UploadIntent';

export interface UploadIntentRepository {
  save(user: UploadIntent): Promise<void>;
}

export const UPLOAD_INTENT_REPOSITORY = Symbol('UploadIntentRepository');
