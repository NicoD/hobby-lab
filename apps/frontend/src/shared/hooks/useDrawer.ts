import { useCallback, useState } from 'react'

export default function useDrawer<T>(): {isOpen: boolean, item: T | null, open: (it: T) => void, close: (it: T) => void}  {
  const [item, setItem] = useState<T | null>(null)

  const open = useCallback((it: T) => { setItem(it) }, [])
  const close = useCallback(() => { setItem(null) }, [])

  return { isOpen: item !== null, item, open, close }
}
