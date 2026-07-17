import { Injectable } from '@nestjs/common';
import { readFileSync } from 'fs';
import { join } from 'path';
import { UnknownVariantFormatError } from '../domain/MediaErrors';
import type {
  VariantFormat,
  VariantFormatCatalog,
} from '../domain/VariantFormatCatalog';

@Injectable()
export class ConfigVariantFormatCatalog implements VariantFormatCatalog {
  private readonly formats: Record<string, VariantFormat>;

  constructor() {
    const raw = readFileSync(
      join(process.cwd(), 'config', 'variant-formats.json'),
      'utf-8',
    );
    this.formats = JSON.parse(raw) as Record<string, VariantFormat>;
  }

  resolve(name: string): VariantFormat {
    const format = this.formats[name];
    if (!format) {
      throw new UnknownVariantFormatError(name);
    }
    return format;
  }
}
