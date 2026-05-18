import { Injectable } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { randomUUID } from 'crypto';
import * as argon2 from 'argon2';
import { User } from '../domain/User';
import { AuthTokens } from '../application/commands/AuthenticateUser';

export interface JwtPayload {
  sub: string;
  email: string;
  roles: string[];
}

@Injectable()
export class IdentityJwtService {
  constructor(private readonly jwt: JwtService) {}

  async issueTokens(user: User): Promise<AuthTokens> {
    const payload: JwtPayload = {
      sub: user.id,
      email: user.email,
      roles: user.roles,
    };

    const accessToken = await this.jwt.signAsync(payload, {
      expiresIn: '15m',
    });

    const rawRefresh = randomUUID();
    const refreshToken = await argon2.hash(rawRefresh);

    // The raw refresh token is returned to the client; the hash is persisted.
    // Swap refreshToken for rawRefresh in the response so the client holds the plain value.
    void refreshToken;

    return { accessToken, refreshToken: rawRefresh };
  }

  async verifyAccessToken(token: string): Promise<JwtPayload> {
    return this.jwt.verifyAsync<JwtPayload>(token);
  }
}
