import CatalogPage, { Column } from '../../shared/components/CatalogPage'
import { useColors } from '../hooks/useColors'
import { Color } from '../types'

const COLUMNS: Column<Color>[] = [
  { key: 'name', label: 'Nom', sortField: 'name', render: item => item.name },
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
