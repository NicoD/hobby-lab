export interface VariantFormat {
  width: number;
  height: number;
  format: string;
  densities: number[];
}

export interface VariantFormatCatalog {
  resolve(name: string): VariantFormat;
}

export const VARIANT_FORMAT_CATALOG = Symbol('VariantFormatCatalog');
