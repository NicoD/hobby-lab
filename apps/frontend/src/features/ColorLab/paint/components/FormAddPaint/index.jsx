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
            brand: filters.brand,
            range: filters.range,
            paintType: filters.type,
            color: filters.color,
        })
        return handle
    }, [createPaintReference.mutateAsync, filters])

    const handleSubmit = async (e) => {
        e.preventDefault()
        await createPaint.mutateAsync({ paintReferenceHandle: paintReferenceId, purchasedAt: purchasedAt || null })
        onClose()
    }

    return <form onSubmit={handleSubmit}>
        <FormField label="Marque" error={createBrand.isError ? createBrand.error.message : null}>
            <Combobox
                values={existingBrands.map(brand => ({ key: brand.handle, value: brand.name }))}
                value={filters.brand}
                onChange={(brand) => setFilters(prev => ({ ...prev, brand, range: undefined }))}
                onCreate={handleCreateBrand}
                placeholder="Marque…"
            />
        </FormField>

        {currentBrand?.ranges?.length ? (
            <FormField label="Gamme">
                <Combobox
                    values={currentBrand.ranges.map(range => ({ key: range.handle, value: range.name }))}
                    value={filters.range}
                    onChange={(range) => setFilters(prev => ({ ...prev, range }))}
                    placeholder="Gamme…"
                />
            </FormField>
        ) : null}

        <FormField label="Type" error={createPaintType.isError ? createPaintType.error.message : null}>
            <Combobox
                values={existingPaintTypes.map(paintType => ({ key: paintType.handle, value: paintType.name }))}
                value={filters.type}
                onChange={(type) => setFilters(prev => ({ ...prev, type }))}
                onCreate={handleCreatePaintType}
                placeholder="Type…"
            />
        </FormField>

        <FormField label="Couleur" error={createColor.isError ? createColor.error.message : null}>
            <Combobox
                values={existingColors.map(color => ({ key: color.handle, value: color.name }))}
                value={filters.color}
                onChange={(color) => setFilters(prev => ({ ...prev, color }))}
                onCreate={handleCreateColor}
                placeholder="Couleur…"
            />
        </FormField>

        {filters.brand && (
            <FormField label="Référence" error={createPaintReference.isError ? createPaintReference.error.message : null}>
                <Combobox
                    values={existingPaintReferences.map(ref => ({ key: ref.handle, value: ref.name }))}
                    value={paintReferenceId}
                    onChange={setPaintReferenceId}
                    onCreate={handleCreatePaintReference}
                    placeholder="Référence…"
                />
            </FormField>
        )}

        <FormField label="Date d'achat">
            <input
                type="date"
                aria-label="Date d'achat"
                value={purchasedAt}
                onChange={(e) => setPurchasedAt(e.target.value)}
                disabled={!paintReferenceId}
            />
        </FormField>

        {createPaint.isError && (
            <p className="mb-4 text-sm text-red-600">{createPaint.error.message}</p>
        )}

        <div className="flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button
                type="button"
                onClick={onClose}
                className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
                Annuler
            </button>
            <button
                type="submit"
                disabled={!paintReferenceId || createPaint.isPending}
                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 active:bg-indigo-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
            >
                Ajouter
            </button>
        </div>
    </form>
}
