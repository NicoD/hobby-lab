import { Role } from './Role';
import { Token } from './Token';

export class User {
  private constructor(
    readonly id: string,
    readonly email: string,
    readonly passwordHash: string,
    readonly roles: Role[],
    readonly tokens: Token[],
    readonly createdAt: Date,
  ) {}

  static create(params: { id: string; email: string; passwordHash: string }): User {
    return new User(params.id, params.email, params.passwordHash, [Role.USER], [], new Date());
  }

  static reconstitute(params: {
    id: string;
    email: string;
    passwordHash: string;
    roles: Role[];
    tokens: Token[];
    createdAt: Date;
  }): User {
    return new User(
      params.id,
      params.email,
      params.passwordHash,
      params.roles,
      params.tokens,
      params.createdAt,
    );
  }

  hasRole(role: Role): boolean {
    return this.roles.includes(role);
  }
}
