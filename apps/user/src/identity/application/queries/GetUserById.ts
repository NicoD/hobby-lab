import { Inject, Injectable, NotFoundException } from '@nestjs/common';
import { USER_REPOSITORY, UserRepository } from '../../domain/UserRepository';
import { User } from '../../domain/User';

export class GetUserByIdQuery {
  constructor(readonly userId: string) {}
}

@Injectable()
export class GetUserByIdHandler {
  constructor(@Inject(USER_REPOSITORY) private readonly users: UserRepository) {}

  async execute(query: GetUserByIdQuery): Promise<User> {
    const user = await this.users.findById(query.userId);
    if (!user) {
      throw new NotFoundException('User not found');
    }
    return user;
  }
}
