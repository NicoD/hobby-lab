import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ListResponse, useApiFetch } from '../../../../../../shared/hooks/useApiFetch';
import { type CatalogPaintType } from '../../types';

export function useCatalogPaintTypes() {
  const apiFetch = useApiFetch();
  const queryClient = useQueryClient();

  const { data } = useQuery({
    queryKey: ['colorlab-catalog-paint-types'],
    queryFn: () =>
      apiFetch<ListResponse<CatalogPaintType>>('/api/color-lab/catalog/paint-types').then(
        (r) => r ?? undefined,
      ),
  });

  const createPaintType = useMutation({
    mutationFn: (paintType: { name: string }) =>
      apiFetch<CatalogPaintType>('/api/color-lab/catalog/paint-types', {
        method: 'POST',
        body: JSON.stringify(paintType),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['colorlab-catalog-paint-types'] }),
  });

  return { paintTypes: data?.items ?? [], createPaintType };
}
