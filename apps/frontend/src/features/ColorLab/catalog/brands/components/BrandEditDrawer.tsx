import Drawer from '../../../../../shared/components/Drawer';
import { Brand } from '../types';

type BrandEditDrawerProps = {
  brand: Brand | null;
  onClose: () => void;
};

export default function BrandEditDrawer({ brand, onClose }: BrandEditDrawerProps) {
  return (
    <Drawer isOpen={brand !== null} onClose={onClose} title="Modifier la marque">
      <p className="text-sm text-gray-400">Formulaire à venir.</p>
    </Drawer>
  );
}
