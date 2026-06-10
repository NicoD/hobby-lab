import { useEffect } from 'react'
import { createPortal } from 'react-dom'

export default function Modal({ isOpen, close, title, children }) {
  // Bloquer le scroll du body quand la modal est ouverte
  useEffect(() => {
    if (!isOpen) return
    document.body.style.overflow = 'hidden'
    return () => { document.body.style.overflow = '' }
  }, [isOpen])

  if (!isOpen) return null

  return createPortal(
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      aria-modal="true"
      role="dialog"
    >
      <div
        className="absolute inset-0 bg-black/50"
        onClick={close}
      />

      <div className="relative z-10 w-full max-w-md bg-white rounded-xl shadow-xl flex flex-col max-h-[90vh]">
        
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
          <h2 className="text-lg font-semibold text-gray-900">{title}</h2>
          <button
            onClick={close}
            className="text-gray-400 hover:text-gray-600 transition-colors"
            aria-label="Fermer"
          >
            ✕
          </button>
        </div>

        <div className="overflow-y-auto p-6">
          {children}
        </div>
      </div>
    </div>,
    document.body
  )
}