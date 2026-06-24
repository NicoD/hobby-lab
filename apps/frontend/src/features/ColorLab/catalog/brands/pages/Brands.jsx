import CatalogPage from '../../shared/components/CatalogPage'
import { useBrands } from '../hooks/useBrands'

const COLUMNS = [
  { key: 'name', label: 'Nom', sortField: 'name' },
  {
    key: 'ranges',
    label: 'Gammes',
    render: item => item.ranges.length > 0
      ? item.ranges.map(r => r.name).join(', ')
      : <span className="text-gray-300">—</span>,
  },
  { key: 'createdAt', label: 'Créé le', sortField: 'createdAt', render: item => new Date(item.createdAt).toLocaleDateString('fr-FR') },
]

export default function Brands() {
  const catalog = useBrands()

  return (
    <CatalogPage
      title="Marques"
      columns={COLUMNS}
      {...catalog}
    />
  )
}
