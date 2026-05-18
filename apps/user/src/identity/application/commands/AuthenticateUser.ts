import { Inject, Injectable, UnauthorizedException } from '@nestjs/common';
import { IsEmail, IsString } from 'class-validator';
import * as argon2 from 'argon2';
import { USER_REPOSITORY, UserRepository } from '../../domain/UserRepository';
import { IdentityJwtService } from '../../infrastructure/JwtService';

export class AuthenticateUserCommand {
  @IsEmail()
  email: string;

  @IsString()
  password: string;
}

export interface AuthTokens {
  accessToken: string;
  refreshToken: string;
}

@Injectable()
export class AuthenticateUserHandler {
  constructor(
    @Inject(USER_REPOSITORY) private readonly users: UserRepository,
    private readonly jwtService: IdentityJwtService,
  ) {}

  async execute(command: AuthenticateUserCommand): Promise<AuthTokens> {
    const user = await this.users.findByEmail(command.email);
    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }

    const valid = await argon2.verify(user.passwordHash, command.password);
    if (!valid) {
      throw new UnauthorizedException('Invalid credentials');
    }

    return this.jwtService.issueTokens(user);
  }
}
