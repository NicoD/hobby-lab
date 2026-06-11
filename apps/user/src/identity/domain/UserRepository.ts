import { User } from './User';
import { Token } from './Token';

export interface UserRepository {
  findById(id: string): Promise<User | null>;
  findByEmail(email: string): Promise<User | null>;
  save(user: User): Promise<void>;
  saveToken(token: Token): Promise<void>;
  findTokenByHash(hash: string): Promise<Token | null>;
  revokeToken(id: string): Promise<void>;
  revokeAllTokensForUser(userId: string): Promise<void>;
}

export const USER_REPOSITORY = Symbol('UserRepository');
