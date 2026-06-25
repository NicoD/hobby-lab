import CatalogPage, { Column } from '../../shared/components/CatalogPage'
import { useCatalogPaints } from '../hooks/useCatalogPaints'
import { Paint } from '../types'

const dash = <span className="text-gray-300">—</span>

const COLUMNS: Column<Paint>[] = [
  { key: 'name', label: 'Nom', sortField: 'name', render: item => item.name},
  { key: 'brandName', label: 'Marque', render: item => item.brandName ?? dash },
  { key: 'colorName', label: 'Couleur', render: item => item.colorName ?? dash },
  { key: 'paintTypeName', label: 'Type', render: item => item.paintTypeName ?? dash },
  {
    key: 'createdAt',
    label: 'Créé le',
    sortField: 'createdAt',
    render: item => new Date(item.createdAt).toLocaleDateString('fr-FR'),
  },
]

export default function CatalogPaints() {
  const catalog = useCatalogPaints()

  return (
    <CatalogPage
      title="Références"
      columns={COLUMNS}
      {...catalog}
    />
  )
}
