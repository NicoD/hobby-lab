import { Injectable } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { createHash, randomUUID } from 'crypto';
import { User } from '../domain/User';

export interface JwtPayload {
  sub: string;
  email: string;
  roles: string[];
}

export interface IssuedTokens {
  accessToken: string;
  rawRefreshToken: string;
  hashedRefreshToken: string;
}

@Injectable()
export class IdentityJwtService {
  constructor(private readonly jwt: JwtService) {}

  async issueTokens(user: User): Promise<IssuedTokens> {
    const payload: JwtPayload = {
      sub: user.id,
      email: user.email,
      roles: user.roles,
    };

    const accessToken = await this.jwt.signAsync(payload, {
      expiresIn: '15m',
    });

    const rawRefreshToken = randomUUID();
    const hashedRefreshToken = createHash('sha256').update(rawRefreshToken).digest('hex');

    return { accessToken, rawRefreshToken, hashedRefreshToken };
  }

  async verifyAccessToken(token: string): Promise<JwtPayload> {
    return this.jwt.verifyAsync<JwtPayload>(token);
  }
}
