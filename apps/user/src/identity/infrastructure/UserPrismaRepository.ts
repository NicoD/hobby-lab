import { Injectable } from '@nestjs/common';
import { UserRepository } from '../domain/UserRepository';
import { User } from '../domain/User';
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
        roles: user.roles,
        createdAt: user.createdAt,
      },
      update: {
        email: user.email,
        passwordHash: user.passwordHash,
        roles: user.roles,
      },
    });
  }

  async saveToken(token: Token): Promise<void> {
    await this.prisma.token.create({
      data: {
        id: token.id,
        userId: token.userId,
        hashedToken: token.hashedToken,
        expiresAt: token.expiresAt,
      },
    });
  }

  async findTokenByHash(hash: string): Promise<Token | null> {
    const row = await this.prisma.token.findFirst({ where: { hashedToken: hash } });
    if (!row) return null;
    return new Token(row.id, row.userId, row.hashedToken, row.expiresAt, row.revokedAt);
  }

  async revokeToken(id: string): Promise<void> {
    await this.prisma.token.update({
      where: { id },
      data: { revokedAt: new Date() },
    });
  }

  async revokeAllTokensForUser(userId: string): Promise<void> {
    await this.prisma.token.updateMany({
      where: { userId, revokedAt: null },
      data: { revokedAt: new Date() },
    });
  }

  private toDomain(row: any): User {
    return User.reconstitute({
      id: row.id,
      email: row.email,
      passwordHash: row.passwordHash,
      roles: row.roles as Role[],
      tokens: (row.tokens ?? []).map(
        (t: any) => new Token(t.id, t.userId, t.hashedToken, t.expiresAt, t.revokedAt),
      ),
      createdAt: row.createdAt,
    });
  }
}
