import { useCallback } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useAuth } from '../../../../../../shared/context/AuthContext';
import { ListResponse, useApiFetch } from '../../../../../../shared/hooks/useApiFetch';
import { CatalogBrand, CatalogColor, StashPaint, CatalogPaintType } from '../../types';

export function useFormAddPaint() {
  const { token } = useAuth();
  const apiFetch = useApiFetch();
  const queryClient = useQueryClient();

  const { data: brandsData } = useQuery({
    queryKey: ['brands', token],
    queryFn: () => apiFetch<ListResponse<CatalogBrand>>('/api/color-lab/catalog/brands'),
    enabled: !!token,
  });
  const existingBrands = brandsData?.items ?? [];

  const { data: paintTypesData } = useQuery({
    queryKey: ['paint-types', token],
    queryFn: () =>
      apiFetch<ListResponse<CatalogPaintType>>('/api/color-lab/catalog/paint-types').then(
        (r) => r ?? undefined,
      ),
    enabled: !!token,
  });
  const existingPaintTypes = paintTypesData?.items ?? [];

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

  const createBrand = useMutation({
    mutationFn: (brand: { name: string }) =>
      apiFetch<CatalogBrand>('/api/color-lab/catalog/brands', {
        method: 'POST',
        body: JSON.stringify(brand),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['brands'] }),
  });

  const createPaintType = useMutation({
    mutationFn: (paintType: { name: string }) =>
      apiFetch<CatalogPaintType>('/api/color-lab/catalog/paint-types', {
        method: 'POST',
        body: JSON.stringify(paintType),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['paint-types'] }),
  });

  const createColor = useMutation({
    mutationFn: (color: { name: string }) =>
      apiFetch<CatalogColor>('/api/color-lab/catalog/colors', {
        method: 'POST',
        body: JSON.stringify(color),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['colors'] }),
  });

  const createPaint = useMutation({
    mutationFn: (paint: { paintHandle: string | null; purchasedAt: string | null }) =>
      apiFetch<StashPaint>('/api/color-lab/stash/paints', {
        method: 'POST',
        body: JSON.stringify(paint),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['stash-paints'] }),
  });

  return {
    existingBrands,
    existingPaintTypes,
    searchColors,
    createBrand,
    createPaintType,
    createColor,
    createPaint,
  };
}
