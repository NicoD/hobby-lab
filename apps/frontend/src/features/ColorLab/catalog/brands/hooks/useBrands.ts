import { useState } from 'react'
import { useQuery, keepPreviousData } from '@tanstack/react-query'
import { useApiFetch } from '../../../../../shared/hooks/useApiFetch'
import { useDebounce } from '../../../../../shared/hooks/useDebounce'
import { type Brand } from '../types'

type BrandsResponse = {
  items: Brand[]
  total: number
  limit: number
}

export function useBrands() {
  const apiFetch = useApiFetch<BrandsResponse>()
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [sort, setSort] = useState('name')
  const [dir, setDir] = useState('asc')

  const debouncedSearch = useDebounce(search)

  const query = useQuery({
    queryKey: ['brands', debouncedSearch, page, sort, dir],
    queryFn: () => {
      const params = new URLSearchParams({ page: page.toString(), sort, dir })
      if (debouncedSearch) params.set('search', debouncedSearch)
      return apiFetch(`/api/color-lab/catalog/brands?${params}`).then(r => r ?? undefined)
    },
    placeholderData: keepPreviousData,
  })

  function handleSearch(value: string) {
    setSearch(value)
    setPage(1)
  }

  function handleSort(field: string) {
    if (field === sort) {
      setDir(d => d === 'asc' ? 'desc' : 'asc')
    } else {
      setSort(field)
      setDir('asc')
    }
    setPage(1)
  }

  return { query, search, onSearch: handleSearch, page, onPage: setPage, sort, dir, onSort: handleSort }
}
