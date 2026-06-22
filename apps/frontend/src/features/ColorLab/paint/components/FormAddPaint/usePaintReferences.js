import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuth } from "../../../../../shared/context/AuthContext";
import { useApiFetch } from "../../../../../shared/hooks/useApiFetch";

export function usePaintReferences(criteria = {}) {
    const { token } = useAuth()
    const apiFetch = useApiFetch()
    const queryClient = useQueryClient()

    const { data: paintReferences = [] } = useQuery({
        queryKey: ['paint-references', token, criteria.brand, criteria.range, criteria.type, criteria.color],
        queryFn: () => {
            const params = new URLSearchParams()
            if (criteria.brand) params.set('brand', criteria.brand)
            if (criteria.range) params.set('range', criteria.range)
            if (criteria.type) params.set('type', criteria.type)
            if (criteria.color) params.set('color', criteria.color)
            return apiFetch(`/api/color-lab/paint-references?${params}`)
        },
        enabled: !!token && !!criteria.brand,
    })

    const createPaintReference = useMutation({
        mutationFn: (reference) => apiFetch('/api/color-lab/paint-references', {
            method: 'POST',
            body: JSON.stringify(reference),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['paint-references'] }),
    })

    return { paintReferences, createPaintReference }
}
