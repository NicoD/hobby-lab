import {
  Controller,
  Post,
  Get,
  Body,
  Req,
  Res,
  HttpCode,
  HttpStatus,
  UnauthorizedException,
  Inject,
} from '@nestjs/common';
import { createHash, randomUUID } from 'crypto';
import { Request, Response } from 'express';
import {
  AuthenticateUserCommand,
  AuthenticateUserHandler,
} from '../application/commands/AuthenticateUser';
import { RegisterUserCommand, RegisterUserHandler } from '../application/commands/RegisterUser';
import { IdentityJwtService } from '../infrastructure/JwtService';
import { USER_REPOSITORY, UserRepository } from '../domain/UserRepository';
import { Token } from '../domain/Token';

const REFRESH_TOKEN_TTL_MS = 7 * 24 * 60 * 60 * 1000;

const COOKIE_OPTIONS = {
  httpOnly: true,
  secure: process.env.NODE_ENV === 'production',
  sameSite: 'strict' as const,
  maxAge: REFRESH_TOKEN_TTL_MS,
  path: '/',
};

@Controller()
export class AuthController {
  constructor(
    private readonly authenticateHandler: AuthenticateUserHandler,
    private readonly registerHandler: RegisterUserHandler,
    private readonly jwtService: IdentityJwtService,
    @Inject(USER_REPOSITORY) private readonly users: UserRepository,
  ) {}

  @Post('auth/login')
  @HttpCode(HttpStatus.OK)
  async login(@Body() command: AuthenticateUserCommand, @Res({ passthrough: true }) res: Response) {
    const { accessToken, rawRefreshToken } = await this.authenticateHandler.execute(command);
    res.cookie('refreshToken', rawRefreshToken, COOKIE_OPTIONS);
    return { accessToken };
  }

  @Post('auth/register')
  @HttpCode(HttpStatus.CREATED)
  register(@Body() command: RegisterUserCommand) {
    return this.registerHandler.execute(command);
  }

  @Post('auth/refresh')
  @HttpCode(HttpStatus.OK)
  async refresh(@Req() req: Request, @Res({ passthrough: true }) res: Response) {
    const raw: string | undefined = req.cookies?.refreshToken;
    if (!raw) throw new UnauthorizedException();

    const hash = createHash('sha256').update(raw).digest('hex');
    const token = await this.users.findTokenByHash(hash);
    if (!token) throw new UnauthorizedException();

    // A revoked token being replayed means the rotation chain leaked:
    // either the legitimate user or a thief is holding a stale copy.
    // Kill every session for this user so the thief loses access too.
    if (token.isRevoked()) {
      await this.users.revokeAllTokensForUser(token.userId);
      throw new UnauthorizedException();
    }
    if (token.isExpired()) throw new UnauthorizedException();

    const user = await this.users.findById(token.userId);
    if (!user) throw new UnauthorizedException();

    await this.users.revokeToken(token.id);

    const { accessToken, rawRefreshToken, hashedRefreshToken } =
      await this.jwtService.issueTokens(user);
    const newToken = new Token(
      randomUUID(),
      user.id,
      hashedRefreshToken,
      new Date(Date.now() + REFRESH_TOKEN_TTL_MS),
    );
    await this.users.saveToken(newToken);

    res.cookie('refreshToken', rawRefreshToken, COOKIE_OPTIONS);
    return { accessToken };
  }

  @Post('auth/logout')
  @HttpCode(HttpStatus.NO_CONTENT)
  async logout(@Req() req: Request, @Res({ passthrough: true }) res: Response) {
    const raw: string | undefined = req.cookies?.refreshToken;
    if (raw) {
      const hash = createHash('sha256').update(raw).digest('hex');
      const token = await this.users.findTokenByHash(hash);
      if (token) await this.users.revokeToken(token.id);
    }
    res.clearCookie('refreshToken', { path: '/' });
  }

  /** ForwardAuth endpoint called by Traefik to validate every inbound request. */
  @Get('validate')
  async validate(@Req() req: Request, @Res({ passthrough: true }) res: Response) {
    const authHeader = req.headers['authorization'];
    if (!authHeader?.startsWith('Bearer ')) {
      throw new UnauthorizedException();
    }

    const token = authHeader.slice(7);
    let payload: Awaited<ReturnType<IdentityJwtService['verifyAccessToken']>>;
    try {
      payload = await this.jwtService.verifyAccessToken(token);
    } catch {
      throw new UnauthorizedException();
    }

    res.setHeader('X-User-Id', payload.sub);
    res.setHeader('X-User-Roles', payload.roles.join(','));
  }
}
