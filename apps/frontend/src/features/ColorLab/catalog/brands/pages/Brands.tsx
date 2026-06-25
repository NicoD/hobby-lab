import CatalogPage, { Column } from '../../shared/components/CatalogPage'
import { useBrands } from '../hooks/useBrands'
import { type Brand } from '../types'
import BrandEditDrawer from '../components/BrandEditDrawer'
import useDrawer from '../../../../../shared/hooks/useDrawer'

const COLUMNS: Column<Brand>[] = [
  { key: 'name', label: 'Nom', sortField: 'name', render: item => item.name },
  {
    key: 'ranges',
    label: 'Gammes',
    render: item => item.ranges.length > 0
      ? item.ranges.map(r => r.name).join(', ')
      : <span className="text-gray-300">—</span>,
  },
  { key: 'createdAt', label: 'Créé le', sortField: 'createdAt', render: item => new Date(item.createdAt).toLocaleDateString('fr-FR') },
]

function EditIcon() {
  return (
    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
    </svg>
  )
}

export default function Brands() {
  const catalog = useBrands()
  const drawer = useDrawer<Brand>()

  return (
    <>
      <CatalogPage
        title="Marques"
        columns={COLUMNS}
        rowActions={(item: Brand) => (
          <button
            onClick={() => { drawer.open(item) }}
            className="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
            aria-label="Modifier"
          >
            <EditIcon />
          </button>
        )}
        {...catalog}
      />
      <BrandEditDrawer brand={drawer.item} onClose={drawer.close} />
    </>
  )
}
