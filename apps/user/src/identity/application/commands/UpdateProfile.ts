import { Inject, Injectable, NotFoundException } from '@nestjs/common';
import { IsOptional, IsString, IsUrl } from 'class-validator';
import { USER_REPOSITORY, UserRepository } from '../../domain/UserRepository';
import { Profile } from '../../domain/Profile';

export class UpdateProfileCommand {
  userId: string;

  @IsOptional()
  @IsString()
  firstName?: string;

  @IsOptional()
  @IsString()
  lastName?: string;

  @IsOptional()
  @IsUrl()
  avatar?: string;
}

@Injectable()
export class UpdateProfileHandler {
  constructor(
    @Inject(USER_REPOSITORY) private readonly users: UserRepository,
  ) {}

  async execute(command: UpdateProfileCommand): Promise<void> {
    const user = await this.users.findById(command.userId);
    if (!user) {
      throw new NotFoundException('User not found');
    }

    const updatedProfile = new Profile(
      command.firstName ?? user.profile.firstName,
      command.lastName ?? user.profile.lastName,
      command.avatar ?? user.profile.avatar,
    );

    await this.users.save(user.updateProfile(updatedProfile));
  }
}
