import {
  Controller,
  Post,
  Get,
  Body,
  Req,
  HttpCode,
  HttpStatus,
  UnauthorizedException,
} from '@nestjs/common';
import { Request } from 'express';
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
  async validate(@Req() req: Request) {
    const authHeader = req.headers['authorization'];
    if (!authHeader?.startsWith('Bearer ')) {
      throw new UnauthorizedException();
    }

    const token = authHeader.slice(7);
    const payload = await this.jwtService.verifyAccessToken(token);

    return { sub: payload.sub, roles: payload.roles };
  }
}
