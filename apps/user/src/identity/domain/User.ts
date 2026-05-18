import { Profile } from './Profile';
import { Role } from './Role';
import { Token } from './Token';

export class User {
  private constructor(
    readonly id: string,
    readonly email: string,
    readonly passwordHash: string,
    readonly profile: Profile,
    readonly roles: Role[],
    readonly tokens: Token[],
    readonly createdAt: Date,
  ) {}

  static create(params: {
    id: string;
    email: string;
    passwordHash: string;
    profile: Profile;
  }): User {
    return new User(
      params.id,
      params.email,
      params.passwordHash,
      params.profile,
      [Role.USER],
      [],
      new Date(),
    );
  }

  static reconstitute(params: {
    id: string;
    email: string;
    passwordHash: string;
    profile: Profile;
    roles: Role[];
    tokens: Token[];
    createdAt: Date;
  }): User {
    return new User(
      params.id,
      params.email,
      params.passwordHash,
      params.profile,
      params.roles,
      params.tokens,
      params.createdAt,
    );
  }

  updateProfile(profile: Profile): User {
    return User.reconstitute({ ...this.snapshot(), profile });
  }

  hasRole(role: Role): boolean {
    return this.roles.includes(role);
  }

  private snapshot() {
    return {
      id: this.id,
      email: this.email,
      passwordHash: this.passwordHash,
      profile: this.profile,
      roles: this.roles,
      tokens: this.tokens,
      createdAt: this.createdAt,
    };
  }
}
