import { useCallback } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuth } from "../../../../../../shared/context/AuthContext";
import { useApiFetch } from "../../../../../../shared/hooks/useApiFetch";

export function useFormAddPaint() {
    const { token } = useAuth()
    const apiFetch = useApiFetch()
    const queryClient = useQueryClient()

    const { data: brandsData } = useQuery({
        queryKey: ['brands', token],
        queryFn: () => apiFetch('/api/color-lab/catalog/brands'),
        enabled: !!token,
    })
    const existingBrands = brandsData?.items ?? []

    const { data: existingPaintTypes = [] } = useQuery({
        queryKey: ['paint-types', token],
        queryFn: () => apiFetch('/api/color-lab/catalog/paint-types'),
        enabled: !!token,
    })

    const searchColors = useCallback(async (query) => {
        const params = new URLSearchParams({ search: query })
        const data = await apiFetch(`/api/color-lab/catalog/colors?${params}`)
        return data.items.map(c => ({ key: c.handle, value: c.name }))
    }, [apiFetch])

    const createBrand = useMutation({
        mutationFn: (brand) => apiFetch('/api/color-lab/catalog/brands', {
            method: 'POST',
            body: JSON.stringify(brand),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['brands'] }),
    })

    const createPaintType = useMutation({
        mutationFn: (paintType) => apiFetch('/api/color-lab/catalog/paint-types', {
            method: 'POST',
            body: JSON.stringify(paintType),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['paint-types'] }),
    })

    const createColor = useMutation({
        mutationFn: (color) => apiFetch('/api/color-lab/catalog/colors', {
            method: 'POST',
            body: JSON.stringify(color),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['colors'] }),
    })

    const createPaint = useMutation({
        mutationFn: (paint) => apiFetch('/api/color-lab/stash/paints', {
            method: 'POST',
            body: JSON.stringify(paint),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['stash-paints'] }),
    })

    return { existingBrands, existingPaintTypes, searchColors, createBrand, createPaintType, createColor, createPaint }
}
