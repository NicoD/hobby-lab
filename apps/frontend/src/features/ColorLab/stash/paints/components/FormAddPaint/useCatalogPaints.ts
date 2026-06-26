import { useCallback } from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { ListResponse, useApiFetch } from '../../../../../../shared/hooks/useApiFetch';
import { CatalogPaint } from '../../types';

export type Criteria = {
  brand?: string | null;
  range?: string | null;
  type?: string | null;
  color?: string | null;
};

type SearchResult = {
  key: string;
  value: string;
};

export function useCatalogPaints(criteria: Criteria = {}) {
  const apiFetch = useApiFetch();
  const queryClient = useQueryClient();

  const searchCatalogPaints = useCallback(
    async (query: string): Promise<SearchResult[]> => {
      const params = new URLSearchParams({ search: query });
      if (criteria.brand) params.set('brand', criteria.brand);
      if (criteria.range) params.set('range', criteria.range);
      if (criteria.type) params.set('type', criteria.type);
      if (criteria.color) params.set('color', criteria.color);
      const data = await apiFetch<ListResponse<CatalogPaint>>(
        `/api/color-lab/catalog/paints?${params}`,
      );
      return data?.items.map((ref) => ({ key: ref.handle, value: ref.name })) ?? [];
    },
    [apiFetch, criteria.brand, criteria.range, criteria.type, criteria.color],
  );

  const createCatalogPaint = useMutation({
    mutationFn: (paint: {
      name: string;
      brand: string | null;
      range: string | null;
      paintType: string | null;
      color: string | null;
    }) =>
      apiFetch<CatalogPaint>('/api/color-lab/catalog/paints', {
        method: 'POST',
        body: JSON.stringify(paint),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['colorlab-catalog-paints'] }),
  });

  return { searchCatalogPaints, createCatalogPaint };
}
