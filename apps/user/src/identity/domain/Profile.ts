export class Profile {
  constructor(
    readonly firstName: string,
    readonly lastName: string,
    readonly avatar: string | null,
  ) {}

  withAvatar(avatar: string): Profile {
    return new Profile(this.firstName, this.lastName, avatar);
  }

  fullName(): string {
    return `${this.firstName} ${this.lastName}`.trim();
  }
}
