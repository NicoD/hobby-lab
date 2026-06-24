import ListWrapper from '../../../../../shared/components/ListWrapper'
import CatalogPagination from './CatalogPagination'

export default function CatalogPage({ title, columns, query, search, onSearch, sort, dir, onSort, page, onPage, actions, rowActions }) {
  const { data, isLoading, isError } = query
  const items = data?.items ?? []

  return (
    <ListWrapper title={title} actions={actions}>
      <div className="flex items-center gap-3 mb-4">
        <div className="relative">
          <svg className="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            type="search"
            value={search}
            onChange={e => onSearch(e.target.value)}
            placeholder="Rechercher…"
            className="pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-300 w-56"
          />
        </div>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-200">
              {columns.map(col => (
                <th
                  key={col.key}
                  className={`text-left py-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-400 select-none ${col.sortField ? 'cursor-pointer hover:text-gray-600' : ''}`}
                  onClick={col.sortField ? () => onSort(col.sortField) : undefined}
                >
                  <span className="inline-flex items-center gap-1">
                    {col.label}
                    {col.sortField && <SortIcon active={sort === col.sortField} asc={dir === 'asc'} />}
                  </span>
                </th>
              ))}
              {rowActions && <th className="py-2 px-3 w-0" />}
            </tr>
          </thead>
          <tbody>
            {isLoading && (
              <tr>
                <td colSpan={columns.length} className="py-10 text-center text-sm text-gray-400">Chargement…</td>
              </tr>
            )}
            {isError && (
              <tr>
                <td colSpan={columns.length} className="py-10 text-center text-sm text-red-400">Une erreur est survenue.</td>
              </tr>
            )}
            {!isLoading && !isError && items.length === 0 && (
              <tr>
                <td colSpan={columns.length} className="py-10 text-center text-sm text-gray-400">Aucun résultat.</td>
              </tr>
            )}
            {items.map(item => (
              <tr key={item.handle} className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                {columns.map(col => (
                  <td key={col.key} className="py-2.5 px-3 text-gray-700">
                    {col.render ? col.render(item) : item[col.key]}
                  </td>
                ))}
                {rowActions && (
                  <td className="py-2.5 px-3 text-right">
                    {rowActions(item)}
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {data && (
        <CatalogPagination
          page={page}
          total={data.total}
          limit={data.limit}
          onPage={onPage}
        />
      )}
    </ListWrapper>
  )
}

function SortIcon({ active, asc }) {
  return (
    <span className={`text-[10px] leading-none ${active ? 'text-indigo-500' : 'text-gray-300'}`}>
      {active ? (asc ? '▲' : '▼') : '⇅'}
    </span>
  )
}
