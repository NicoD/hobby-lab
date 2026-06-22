import { useEffect } from 'react'
import { createPortal } from 'react-dom'

export default function Modal({ isOpen, close, title, children }) {
  useEffect(() => {
    if (!isOpen) return
    document.body.style.overflow = 'hidden'
    return () => { document.body.style.overflow = '' }
  }, [isOpen])

  if (!isOpen) return null

  return createPortal(
    <div className="fixed inset-0 z-50" aria-modal="true" role="dialog">
      <div className="fixed inset-0 bg-black/50" />

      {/* The overlay scrolls when the panel outgrows the viewport, so the
          panel itself never needs an inner scrollbar and can hug its content
          (including overlays like combobox dropdowns). */}
      <div className="fixed inset-0 overflow-y-auto" onClick={close}>
        <div className="flex min-h-full items-center justify-center p-4">
          <div
            className="relative w-full max-w-md bg-white rounded-xl shadow-xl"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
              <h2 className="text-lg font-semibold text-gray-900">{title}</h2>
              <button
                onClick={close}
                className="text-gray-400 hover:text-gray-600 transition-colors"
                aria-label="Fermer"
              >
                ✕
              </button>
            </div>

            <div className="p-6">
              {children}
            </div>
          </div>
        </div>
      </div>
    </div>,
    document.body
  )
}