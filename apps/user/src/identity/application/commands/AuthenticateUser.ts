import { Inject, Injectable, UnauthorizedException } from '@nestjs/common';
import { IsEmail, IsString } from 'class-validator';
import { randomUUID } from 'crypto';
import * as argon2 from 'argon2';
import { USER_REPOSITORY, UserRepository } from '../../domain/UserRepository';
import { Token } from '../../domain/Token';
import { IdentityJwtService } from '../../infrastructure/JwtService';

export class AuthenticateUserCommand {
  @IsEmail()
  email: string;

  @IsString()
  password: string;
}

export interface AuthTokens {
  accessToken: string;
  rawRefreshToken: string;
}

// Verified against when the email is unknown, so that "user not found" and
// "wrong password" take the same time and don't leak which emails exist.
const dummyHash = argon2.hash(randomUUID());

@Injectable()
export class AuthenticateUserHandler {
  constructor(
    @Inject(USER_REPOSITORY) private readonly users: UserRepository,
    private readonly jwtService: IdentityJwtService,
  ) {}

  async execute(command: AuthenticateUserCommand): Promise<AuthTokens> {
    const user = await this.users.findByEmail(command.email);

    const valid = await argon2.verify(user?.passwordHash ?? (await dummyHash), command.password);
    if (!user || !valid) {
      throw new UnauthorizedException('Invalid credentials');
    }

    const { accessToken, rawRefreshToken, hashedRefreshToken } =
      await this.jwtService.issueTokens(user);

    const token = new Token(
      randomUUID(),
      user.id,
      hashedRefreshToken,
      new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
    );
    await this.users.saveToken(token);

    return { accessToken, rawRefreshToken };
  }
}
