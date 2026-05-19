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
} from '@nestjs/common';
import { Request, Response } from 'express';
import { AuthenticateUserCommand, AuthenticateUserHandler } from '../application/commands/AuthenticateUser';
import { RegisterUserCommand, RegisterUserHandler } from '../application/commands/RegisterUser';
import { IdentityJwtService } from '../infrastructure/JwtService';

@Controller()
export class AuthController {
  constructor(
    private readonly authenticateHandler: AuthenticateUserHandler,
    private readonly registerHandler: RegisterUserHandler,
    private readonly jwtService: IdentityJwtService,
  ) {}

  @Post('auth/login')
  @HttpCode(HttpStatus.OK)
  login(@Body() command: AuthenticateUserCommand) {
    return this.authenticateHandler.execute(command);
  }

  @Post('auth/register')
  @HttpCode(HttpStatus.CREATED)
  register(@Body() command: RegisterUserCommand) {
    return this.registerHandler.execute(command);
  }

  @Post('auth/refresh')
  @HttpCode(HttpStatus.OK)
  refresh() {
    // TODO: validate refresh token from cookie/body, issue new access token
    throw new UnauthorizedException('Not implemented');
  }

  @Post('auth/logout')
  @HttpCode(HttpStatus.NO_CONTENT)
  logout() {
    // TODO: revoke refresh token
  }

  @Get('auth/jwks')
  jwks() {
    // TODO: return RS256 public key in JWKS format
    return { keys: [] };
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

    // Traefik reads these response headers and injects them into the forwarded request.
    res.setHeader('X-User-Id', payload.sub);
    res.setHeader('X-User-Roles', payload.roles.join(','));
  }
}
