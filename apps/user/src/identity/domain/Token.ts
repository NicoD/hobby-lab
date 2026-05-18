export class Token {
  constructor(
    readonly id: string,
    readonly userId: string,
    readonly hashedToken: string,
    readonly expiresAt: Date,
    readonly revokedAt: Date | null = null,
  ) {}

  isExpired(): boolean {
    return this.expiresAt < new Date();
  }

  isRevoked(): boolean {
    return this.revokedAt !== null;
  }

  isValid(): boolean {
    return !this.isExpired() && !this.isRevoked();
  }
}
