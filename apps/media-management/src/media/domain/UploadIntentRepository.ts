import { UploadIntent } from './UploadIntent';
import type { TransactionContext } from './UnitOfWork';

export interface UploadIntentRepository {
  create(uploadIntent: UploadIntent): Promise<void>;

  findByToken(token: string): Promise<UploadIntent | null>;

  markConsumed(
    token: string,
    userId: string,
    consumedAt: Date,
    tx?: TransactionContext,
  ): Promise<boolean>;
}

export const UPLOAD_INTENT_REPOSITORY = Symbol('UploadIntentRepository');
