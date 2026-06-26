import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ListResponse, useApiFetch } from '../../../../../../shared/hooks/useApiFetch';
import { type CatalogBrand } from '../../types';

export function useCatalogBrands() {
  const apiFetch = useApiFetch();
  const queryClient = useQueryClient();

  const { data } = useQuery({
    queryKey: ['colorlab-catalog-brands'],
    queryFn: () =>
      apiFetch<ListResponse<CatalogBrand>>('/api/color-lab/catalog/brands').then(
        (r) => r ?? undefined,
      ),
  });

  const createBrand = useMutation({
    mutationFn: (brand: { name: string }) =>
      apiFetch<CatalogBrand>('/api/color-lab/catalog/brands', {
        method: 'POST',
        body: JSON.stringify(brand),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['colorlab-catalog-brands'] }),
  });

  return { brands: data?.items ?? [], createBrand };
}
