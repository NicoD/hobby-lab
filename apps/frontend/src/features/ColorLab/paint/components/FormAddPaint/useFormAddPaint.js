import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuth } from "../../../../../shared/context/AuthContext";
import { useApiFetch } from "../../../../../shared/hooks/useApiFetch";

export function useFormAddPaint() {
    const { token } = useAuth()
    const apiFetch = useApiFetch()
    const queryClient = useQueryClient()

    const { data: existingBrands = [] } = useQuery({
        queryKey: ['brands', token],
        queryFn: () => apiFetch('/api/color-lab/brands'),
        enabled: !!token,
    })

    const { data: existingPaintTypes = [] } = useQuery({
        queryKey: ['paint-types', token],
        queryFn: () => apiFetch('/api/color-lab/paint-types'),
        enabled: !!token,
    })

    const { data: existingColors = [] } = useQuery({
        queryKey: ['colors', token],
        queryFn: () => apiFetch('/api/color-lab/colors'),
        enabled: !!token,
    })

    const createBrand = useMutation({
        mutationFn: (brand) => apiFetch('/api/color-lab/brands', {
            method: 'POST',
            body: JSON.stringify(brand),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['brands'] }),
    })

    const createPaintType = useMutation({
        mutationFn: (paintType) => apiFetch('/api/color-lab/paint-types', {
            method: 'POST',
            body: JSON.stringify(paintType),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['paint-types'] }),
    })

    const createColor = useMutation({
        mutationFn: (color) => apiFetch('/api/color-lab/colors', {
            method: 'POST',
            body: JSON.stringify(color),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['colors'] }),
    })

    const createPaint = useMutation({
        mutationFn: (paint) => apiFetch('/api/color-lab/paints', {
            method: 'POST',
            body: JSON.stringify(paint),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['paints'] }),
    })

    return { existingBrands, existingPaintTypes, existingColors, createBrand, createPaintType, createColor, createPaint }
}
