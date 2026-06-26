import { Inject, Injectable, ConflictException } from '@nestjs/common';
import { IsEmail, IsString, MinLength } from 'class-validator';
import * as argon2 from 'argon2';
import { randomUUID } from 'crypto';
import { USER_REPOSITORY, UserRepository } from '../../domain/UserRepository';
import { User } from '../../domain/User';

export class RegisterUserCommand {
  @IsEmail()
  email: string;

  @IsString()
  @MinLength(8)
  password: string;
}

@Injectable()
export class RegisterUserHandler {
  constructor(@Inject(USER_REPOSITORY) private readonly users: UserRepository) {}

  async execute(command: RegisterUserCommand): Promise<{ id: string }> {
    const existing = await this.users.findByEmail(command.email);
    if (existing) {
      throw new ConflictException('Email already in use');
    }

    const passwordHash = await argon2.hash(command.password);
    const user = User.create({
      id: randomUUID(),
      email: command.email,
      passwordHash,
    });

    await this.users.save(user);
    return { id: user.id };
  }
}
