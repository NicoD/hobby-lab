import { useCallback, useState } from "react";
import Combobox from "../../../../../shared/components/Combobox";
import FormField from "../../../../../shared/components/FormField";
import { useFormAddPaint } from "./useFormAddPaint";
import { usePaintReferences } from "./usePaintReferences";

export default function FormAddPaint({ onClose }) {
    const [filters, setFilters] = useState({})
    const [paintReferenceId, setPaintReferenceId] = useState(null)
    const [purchasedAt, setPurchasedAt] = useState('')

    const { existingBrands, existingPaintTypes, existingColors, createBrand, createPaintType, createColor, createPaint } = useFormAddPaint()
    const { paintReferences: existingPaintReferences, createPaintReference } = usePaintReferences(filters)

    const currentBrand = existingBrands.find(brand => filters.brand === brand.handle) ?? null

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

    const handleCreatePaintReference = useCallback(async (name) => {
        const { handle } = await createPaintReference.mutateAsync({
            name,
            brand: paintReference.brand,
            range: paintReference.range,
            paintType: paintReference.type,
            color: paintReference.color,
        })
        return handle
    }, [createPaintReference.mutateAsync, paintReference])

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
        {paintReference.brand && (
            <div className="mb-2">
                <Combobox
                    values={existingPaintReferences.map(ref => ({ key: ref.handle, value: ref.name }))}
                    value={paintReference.reference}
                    onChange={(reference) => setPaintReference(prev => ({ ...prev, reference }))}
                    onCreate={handleCreatePaintReference}
                    placeholder="Référence…"
                />
                {createPaintReference.isError && (
                    <p className="mt-2 text-sm text-red-600">{createPaintReference.error.message}</p>
                )}
            </div>
        )}
    </form>
}
