import CatalogPage from '../../shared/components/CatalogPage'
import { useColors } from '../hooks/useColors'

const COLUMNS = [
  { key: 'name', label: 'Nom', sortField: 'name' },
  { key: 'createdAt', label: 'Créé le', sortField: 'createdAt', render: item => new Date(item.createdAt).toLocaleDateString('fr-FR') },
]

export default function Colors() {
  const catalog = useColors()

  return (
    <CatalogPage
      title="Couleurs"
      columns={COLUMNS}
      {...catalog}
    />
  )
}
