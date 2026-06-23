import { useCallback } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useApiFetch } from "../../../../../shared/hooks/useApiFetch";

export function usePaintReferences(criteria = {}) {
    const apiFetch = useApiFetch()
    const queryClient = useQueryClient()

    const searchPaintReferences = useCallback(async (query) => {
        const params = new URLSearchParams({ search: query })
        if (criteria.brand) params.set('brand', criteria.brand)
        if (criteria.range) params.set('range', criteria.range)
        if (criteria.type) params.set('type', criteria.type)
        if (criteria.color) params.set('color', criteria.color)
        const data = await apiFetch(`/api/color-lab/paint-references?${params}`)
        return data.map(ref => ({ key: ref.handle, value: ref.name }))
    }, [apiFetch, criteria.brand, criteria.range, criteria.type, criteria.color])

    const createPaintReference = useMutation({
        mutationFn: (reference) => apiFetch('/api/color-lab/paint-references', {
            method: 'POST',
            body: JSON.stringify(reference),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['paint-references'] }),
    })

    return { searchPaintReferences, createPaintReference }
}
