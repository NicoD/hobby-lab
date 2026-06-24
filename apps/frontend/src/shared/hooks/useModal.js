import { useCallback, useEffect, useState } from "react";

export default function useModal() {
    const [isOpen, setIsOpen] = useState(false);

    const close = useCallback(() => setIsOpen(false), [])
    const toggle = useCallback(() => setIsOpen(v => !v), [])

    useEffect(() => {
    if (!isOpen) return
    const onKey = (e) => { if (e.key === 'Escape') close() }
    document.body.style.overflow = 'hidden'
    window.addEventListener('keydown', onKey)
    return () => {
      document.body.style.overflow = ''
      window.removeEventListener('keydown', onKey)
    }
  }, [isOpen, close])

  return { isOpen, close, toggle }
}