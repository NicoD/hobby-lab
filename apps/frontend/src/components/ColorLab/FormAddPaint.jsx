import { useCallback, useEffect, useState } from "react";
import Combobox from "../Combobox";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuth } from "../../context/AuthContext";
import { useApiFetch } from "../../hooks/useApiFetch";

function useFormAddPaint() {
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

    return { existingBrands, existingPaintTypes, existingColors, createBrand, createPaintType, createColor }
}

export default function FormAddPaint() {
    const { existingBrands, existingPaintTypes, existingColors, createBrand, createPaintType, createColor } = useFormAddPaint()
    const [paintReference, setPaintReference] = useState({})
    const [currentBrand, setCurrentBrand] = useState(null)

    useEffect(() => {
        if (!paintReference.brand) {
            setCurrentBrand(null)
            return
        }
        setCurrentBrand(existingBrands.find(brand => paintReference.brand === brand.handle) ?? null)
    }, [existingBrands, paintReference.brand])

    const handleCreateBrand = useCallback(async (name) => {
        const { handle } = await createBrand.mutateAsync({ name })
        return handle
    }, [createBrand.mutateAsync])

    const handleCreatePaintType = useCallback(async (name) => {
        const { handle } = await createPaintType.mutateAsync({ name })
        return handle
    }, [createPaintType.mutateAsync])

    const handleCreateColor = useCallback(async (name) => {
        const { handle } = await createColor.mutateAsync({ name })
        return handle
    }, [createColor.mutateAsync])

    return <form>
        <div className="mb-2">
            <Combobox
                values={existingBrands.map(brand => ({ key: brand.handle, value: brand.name }))}
                value={paintReference.brand}
                onChange={(brand) => setPaintReference(prev => ({ ...prev, brand, range: undefined }))}
                onCreate={handleCreateBrand}
                placeholder="Marque…"
            />
            {createBrand.isError && (
                <p className="mt-2 text-sm text-red-600">{createBrand.error.message}</p>
            )}
        </div>
        <div className="mb-2">
            {currentBrand?.ranges?.length ? <Combobox
                values={currentBrand.ranges.map(range => ({ key: range.handle, value: range.name }))}
                value={paintReference.range}
                onChange={(range) => setPaintReference(prev => ({ ...prev, range }))}
                placeholder="Gamme…"
            /> : <></>}
        </div>
        <div className="mb-2">
            <Combobox
                values={existingPaintTypes.map(paintType => ({ key: paintType.handle, value: paintType.name }))}
                value={paintReference.type}
                onChange={(type) => setPaintReference(prev => ({ ...prev, type }))}
                onCreate={handleCreatePaintType}
                placeholder="Type…"
            />
            {createPaintType.isError && (
                <p className="mt-2 text-sm text-red-600">{createPaintType.error.message}</p>
            )}
        </div>
        <div className="mb-2">
            <Combobox
                values={existingColors.map(color => ({ key: color.handle, value: color.name }))}
                value={paintReference.color}
                onChange={(color) => setPaintReference(prev => ({ ...prev, color }))}
                onCreate={handleCreateColor}
                placeholder="Couleur…"
            />
            {createColor.isError && (
                <p className="mt-2 text-sm text-red-600">{createColor.error.message}</p>
            )}
        </div>
    </form>
}
