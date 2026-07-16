import { Media } from './Media';
import type { TransactionContext } from './UnitOfWork';

export interface MediaRepository {
  create(media: Media, tx?: TransactionContext): Promise<void>;

  update(media: Media, tx?: TransactionContext): Promise<void>;

  findByOriginalStorageKey(storageKey: string): Promise<Media | null>;
}

export const MEDIA_REPOSITORY = Symbol('MediaRepository');
