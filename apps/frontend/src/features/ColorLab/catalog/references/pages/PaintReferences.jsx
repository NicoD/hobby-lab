import CatalogPage from '../../shared/components/CatalogPage'
import { usePaintReferences } from '../hooks/usePaintReferences'

const dash = <span className="text-gray-300">—</span>

const COLUMNS = [
  { key: 'name', label: 'Nom', sortField: 'name' },
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

export default function PaintReferences() {
  const catalog = usePaintReferences()

  return (
    <CatalogPage
      title="Références"
      columns={COLUMNS}
      {...catalog}
    />
  )
}
