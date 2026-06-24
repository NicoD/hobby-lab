import { useCallback, useState } from 'react'

export default function useDrawer() {
  const [item, setItem] = useState(null)

  const open = useCallback((it) => setItem(it), [])
  const close = useCallback(() => setItem(null), [])

  return { isOpen: item !== null, item, open, close }
}
