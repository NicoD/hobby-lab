import { useCallback } from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { ListResponse, useApiFetch } from '../../../../../../shared/hooks/useApiFetch';
import { type CatalogColor } from '../../types';

export function useCatalogColors() {
  const apiFetch = useApiFetch();
  const queryClient = useQueryClient();

  const searchColors = useCallback(
    async (query: string) => {
      const params = new URLSearchParams({ search: query });
      const data = await apiFetch<ListResponse<CatalogColor>>(
        `/api/color-lab/catalog/colors?${params}`,
      );
      return data?.items.map((c) => ({ key: c.handle, value: c.name })) ?? [];
    },
    [apiFetch],
  );

  const createColor = useMutation({
    mutationFn: (color: { name: string }) =>
      apiFetch<CatalogColor>('/api/color-lab/catalog/colors', {
        method: 'POST',
        body: JSON.stringify(color),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['catalog-colors'] }),
  });

  return { searchColors, createColor };
}
