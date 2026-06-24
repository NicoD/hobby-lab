import { useCallback, useState } from "react";
import Combobox from "../../../../../../shared/components/Combobox";
import FormField from "../../../../../../shared/components/FormField";
import { useFormAddPaint } from "./useFormAddPaint";
import { useCatalogPaints } from "./useCatalogPaints";

export default function FormAddPaint({ onClose }) {
    const [filters, setFilters] = useState({})
    const [paintHandle, setPaintHandle] = useState(null)
    const [purchasedAt, setPurchasedAt] = useState('')

    const { existingBrands, existingPaintTypes, searchColors, createBrand, createPaintType, createColor, createPaint } = useFormAddPaint()
    const { searchCatalogPaints, createCatalogPaint } = useCatalogPaints(filters)

    const currentBrand = existingBrands.find(brand => filters.brand === brand.handle) ?? null

    const handleCreateBrand = useCallback(async (name) => {
        const { handle } = await createBrand.mutateAsync({ name })
        return handle
    }, [createBrand])

    const handleCreatePaintType = useCallback(async (name) => {
        const { handle } = await createPaintType.mutateAsync({ name })
        return handle
    }, [createPaintType])

    const handleCreateColor = useCallback(async (name) => {
        const { handle } = await createColor.mutateAsync({ name })
        return handle
    }, [createColor])

    const handleCreateCatalogPaint = useCallback(async (name) => {
        const { handle } = await createCatalogPaint.mutateAsync({
            name,
            brand: filters.brand,
            range: filters.range,
            paintType: filters.type,
            color: filters.color,
        })
        return handle
    }, [createCatalogPaint, filters])

    const handleSubmit = async (e) => {
        e.preventDefault()
        await createPaint.mutateAsync({ paintHandle, purchasedAt: purchasedAt || null })
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
                onSearch={searchColors}
                value={filters.color}
                onChange={(color) => setFilters(prev => ({ ...prev, color }))}
                onCreate={handleCreateColor}
                placeholder="Couleur…"
            />
        </FormField>

        {filters.brand && (
            <FormField label="Référence" error={createCatalogPaint.isError ? createCatalogPaint.error.message : null}>
                <Combobox
                    onSearch={searchCatalogPaints}
                    value={paintHandle}
                    onChange={setPaintHandle}
                    onCreate={handleCreateCatalogPaint}
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
                disabled={!paintHandle}
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
                disabled={!paintHandle || createPaint.isPending}
                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 active:bg-indigo-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
            >
                Ajouter
            </button>
        </div>
    </form>
}
