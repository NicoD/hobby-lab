import { useState } from 'react'
import { useQuery, keepPreviousData } from '@tanstack/react-query'
import { useApiFetch } from '../../../../../shared/hooks/useApiFetch'
import { useDebounce } from '../../../../../shared/hooks/useDebounce'

export function useColors() {
  const apiFetch = useApiFetch()
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [sort, setSort] = useState('name')
  const [dir, setDir] = useState('asc')

  const debouncedSearch = useDebounce(search)

  const query = useQuery({
    queryKey: ['colors', debouncedSearch, page, sort, dir],
    queryFn: () => {
      const params = new URLSearchParams({ page, sort, dir })
      if (debouncedSearch) params.set('search', debouncedSearch)
      return apiFetch(`/api/color-lab/colors?${params}`)
    },
    placeholderData: keepPreviousData,
  })

  function handleSearch(value) {
    setSearch(value)
    setPage(1)
  }

  function handleSort(field) {
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
