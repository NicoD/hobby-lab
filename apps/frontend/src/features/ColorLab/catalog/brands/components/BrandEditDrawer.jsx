import Drawer from '../../../../../shared/components/Drawer'

export default function BrandEditDrawer({ brand, onClose }) {
  return (
    <Drawer isOpen={brand !== null} onClose={onClose} title="Modifier la marque">
      <p className="text-sm text-gray-400">Formulaire à venir.</p>
    </Drawer>
  )
}
