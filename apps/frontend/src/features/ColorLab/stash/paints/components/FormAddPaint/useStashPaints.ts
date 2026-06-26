import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useApiFetch } from '../../../../../../shared/hooks/useApiFetch';
import { type StashPaint } from '../../types';

export function useStashPaints() {
  const apiFetch = useApiFetch();
  const queryClient = useQueryClient();

  const createPaint = useMutation({
    mutationFn: (paint: { paintHandle: string | null; purchasedAt: string | null }) =>
      apiFetch<StashPaint>('/api/color-lab/stash/paints', {
        method: 'POST',
        body: JSON.stringify(paint),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['stash-paints'] }),
  });

  return { createPaint };
}
