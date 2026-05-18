import { Injectable } from '@nestjs/common';
import { UserRepository } from '../domain/UserRepository';
import { User } from '../domain/User';
import { Profile } from '../domain/Profile';
import { Role } from '../domain/Role';
import { Token } from '../domain/Token';
import { PrismaService } from './PrismaService';

@Injectable()
export class UserPrismaRepository implements UserRepository {
  constructor(private readonly prisma: PrismaService) {}

  async findById(id: string): Promise<User | null> {
    const row = await this.prisma.user.findUnique({
      where: { id },
      include: { tokens: true },
    });
    return row ? this.toDomain(row) : null;
  }

  async findByEmail(email: string): Promise<User | null> {
    const row = await this.prisma.user.findUnique({
      where: { email },
      include: { tokens: true },
    });
    return row ? this.toDomain(row) : null;
  }

  async save(user: User): Promise<void> {
    await this.prisma.user.upsert({
      where: { id: user.id },
      create: {
        id: user.id,
        email: user.email,
        passwordHash: user.passwordHash,
        firstName: user.profile.firstName,
        lastName: user.profile.lastName,
        avatar: user.profile.avatar,
        roles: user.roles,
        createdAt: user.createdAt,
      },
      update: {
        email: user.email,
        passwordHash: user.passwordHash,
        firstName: user.profile.firstName,
        lastName: user.profile.lastName,
        avatar: user.profile.avatar,
        roles: user.roles,
      },
    });
  }

  private toDomain(row: any): User {
    return User.reconstitute({
      id: row.id,
      email: row.email,
      passwordHash: row.passwordHash,
      profile: new Profile(row.firstName, row.lastName, row.avatar),
      roles: row.roles as Role[],
      tokens: (row.tokens ?? []).map(
        (t: any) =>
          new Token(t.id, t.userId, t.hashedToken, t.expiresAt, t.revokedAt),
      ),
      createdAt: row.createdAt,
    });
  }
}
