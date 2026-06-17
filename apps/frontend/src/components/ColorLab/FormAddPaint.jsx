import { useState } from "react";
import Combobox from "../Combobox";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuth } from "../../context/AuthContext";
import { useApiFetch } from "../../hooks/useApiFetch";

// The API expects a URL-safe handle; POST returns no body, so the handle
// derived here is also what identifies the brand client-side.
function slugify(name) {
    return name
        .trim()
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

export default function FormAddPaint () {
    const { token } = useAuth()
    const apiFetch = useApiFetch()
    const queryClient = useQueryClient()
    const [brandKey, setBrandKey] = useState(null)

    const { data: brands = [], isLoading, error } = useQuery({
        queryKey: ['brands', token],
        queryFn: () => apiFetch('/api/color-lab/brands'),
        enabled: !!token,
    })

    const createBrand = useMutation({
        mutationFn: (brand) => apiFetch('/api/color-lab/brands', {
            method: 'POST',
            body: JSON.stringify(brand),
        }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['brands'] }),
    })

    async function handleCreateBrand(name) {
        const handle = slugify(name)
        await createBrand.mutateAsync({ handle, name })
        return handle
    }

    return <form>
        <Combobox
            values={brands.map(b => ({ key: b.handle, value: b.name }))}
            value={brandKey}
            onChange={setBrandKey}
            onCreate={handleCreateBrand}
            placeholder="Marque…"
        />
        {createBrand.isError && (
            <p className="mt-2 text-sm text-red-600">{createBrand.error.message}</p>
        )}
    </form>
}
