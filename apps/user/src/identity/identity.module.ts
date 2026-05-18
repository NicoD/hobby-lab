import { Module } from '@nestjs/common';
import { JwtModule } from '@nestjs/jwt';
import { USER_REPOSITORY } from './domain/UserRepository';
import { PrismaService } from './infrastructure/PrismaService';
import { UserPrismaRepository } from './infrastructure/UserPrismaRepository';
import { IdentityJwtService } from './infrastructure/JwtService';
import { RegisterUserHandler } from './application/commands/RegisterUser';
import { AuthenticateUserHandler } from './application/commands/AuthenticateUser';
import { UpdateProfileHandler } from './application/commands/UpdateProfile';
import { GetUserByIdHandler } from './application/queries/GetUserById';
import { AuthController } from './interface/AuthController';

@Module({
  imports: [
    JwtModule.register({
      privateKey: process.env.JWT_PRIVATE_KEY,
      publicKey: process.env.JWT_PUBLIC_KEY,
      signOptions: { algorithm: 'RS256' },
    }),
  ],
  controllers: [AuthController],
  providers: [
    PrismaService,
    IdentityJwtService,
    { provide: USER_REPOSITORY, useClass: UserPrismaRepository },
    RegisterUserHandler,
    AuthenticateUserHandler,
    UpdateProfileHandler,
    GetUserByIdHandler,
  ],
  exports: [],
})
export class IdentityModule {}
