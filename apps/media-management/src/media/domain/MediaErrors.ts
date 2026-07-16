export class UnknownVariantFormatError extends Error {
  constructor(name: string) {
    super(`Unknown variant format: ${name}`);
  }
}
