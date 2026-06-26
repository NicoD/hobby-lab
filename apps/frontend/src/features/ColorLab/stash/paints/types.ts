export type StashPaint = {
  paintHandle: string;
};

export type CatalogPaint = {
  handle: string;
  name: string;
  brandName: string | null;
  colorName: string | null;
  paintTypeName: string | null;
  createdAt: string;
};

export type CatalogBrand = {
  handle: string;
  name: string;
  ranges: { handle: string; name: string }[];
};

export type CatalogColor = {
  handle: string;
  name: string;
};

export type CatalogPaintType = {
  handle: string;
  name: string;
};
