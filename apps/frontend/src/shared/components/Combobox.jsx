import { useEffect, useMemo, useRef, useState } from 'react'

/**
 * Searchable select with inline creation.
 *
 * values   – [{ key, value }] collection to pick from
 * value    – selected key (omit to let the component manage its own selection)
 * onChange – (key) => void, called on every selection
 * onCreate – (label) => key | Promise<key>, called when the "Create" option is
 *            picked; if it returns the new key, that key becomes selected
 */
export default function Combobox({
  values = [],
  value,
  onChange,
  onCreate,
  placeholder = 'Search or create…',
}) {
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState('')
  const [activeIndex, setActiveIndex] = useState(0)
  const [internalKey, setInternalKey] = useState(null)
  const rootRef = useRef(null)
  const inputRef = useRef(null)

  const selectedKey = value !== undefined ? value : internalKey
  const selected = values.find(v => v.key === selectedKey)

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase()
    if (!q) return values
    return values.filter(v => String(v.value).toLowerCase().includes(q))
  }, [values, query])

  const canCreate =
    !!onCreate &&
    query.trim() !== '' &&
    !values.some(v => String(v.value).toLowerCase() === query.trim().toLowerCase())

  // Options the keyboard can walk through: filtered items + the create entry.
  const optionCount = filtered.length + (canCreate ? 1 : 0)

  useEffect(() => {
    if (!open) return
    const onPointerDown = (e) => {
      if (!rootRef.current?.contains(e.target)) close()
    }
    document.addEventListener('pointerdown', onPointerDown)
    return () => document.removeEventListener('pointerdown', onPointerDown)
  }, [open])

  useEffect(() => {
    if (open) inputRef.current?.focus()
  }, [open])

  function close() {
    setOpen(false)
    setQuery('')
    setActiveIndex(0)
  }

  function select(key) {
    if (value === undefined) setInternalKey(key)
    onChange?.(key)
    close()
  }

  function reset(e) {
    e.stopPropagation()
    if (value === undefined) setInternalKey(null)
    onChange?.(null)
  }

  async function create() {
    const label = query.trim()
    const newKey = await onCreate(label)
    if (newKey != null) select(newKey)
    else close()
  }

  function pick(index) {
    if (canCreate && index === filtered.length) create()
    else if (filtered[index]) select(filtered[index].key)
  }

  function onKeyDown(e) {
    if (e.key === 'ArrowDown') {
      e.preventDefault()
      setActiveIndex(i => Math.min(i + 1, optionCount - 1))
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      setActiveIndex(i => Math.max(i - 1, 0))
    } else if (e.key === 'Enter') {
      e.preventDefault()
      pick(activeIndex)
    } else if (e.key === 'Escape') {
      e.preventDefault()
      close()
    }
  }

  return (
    <div ref={rootRef} className="relative w-full text-sm">
      <button
        type="button"
        role="combobox"
        aria-expanded={open}
        onClick={() => (open ? close() : setOpen(true))}
        className="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        <span className={selected ? 'text-gray-900' : 'text-gray-400'}>
          {selected ? selected.value : placeholder}
        </span>
        <span className="flex shrink-0 items-center gap-1">
          {selected && (
            <span
              role="button"
              aria-label="Clear selection"
              onClick={reset}
              className="flex items-center text-gray-400 hover:text-gray-600"
            >
              <svg className="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5">
                <path d="M6 6l8 8M14 6l-8 8" strokeLinecap="round" />
              </svg>
            </span>
          )}
          <svg className="size-4 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5">
            <path d="m6 8 4 4 4-4" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </span>
      </button>

      {open && (
        <div className="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg">
          <input
            ref={inputRef}
            value={query}
            onChange={(e) => { setQuery(e.target.value); setActiveIndex(0) }}
            onKeyDown={onKeyDown}
            placeholder={placeholder}
            className="w-full border-b border-gray-200 rounded-t-lg px-3 py-2 focus:outline-none"
          />

          <ul role="listbox" className="max-h-60 overflow-auto p-1">
            {filtered.length === 0 && !canCreate && (
              <li className="px-3 py-2 text-gray-400">No results.</li>
            )}

            {filtered.map((item, index) => (
              <li
                key={item.key}
                role="option"
                aria-selected={item.key === selectedKey}
                onMouseEnter={() => setActiveIndex(index)}
                onClick={() => select(item.key)}
                className={`flex cursor-pointer items-center justify-between rounded-md px-3 py-2 ${
                  index === activeIndex ? 'bg-indigo-50 text-indigo-900' : 'text-gray-700'
                }`}
              >
                <span>{item.value}</span>
                {item.key === selectedKey && (
                  <svg className="size-4 text-indigo-600" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="m5 10.5 3.5 3.5L15 7" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                )}
              </li>
            ))}

            {canCreate && (
              <li
                role="option"
                aria-selected={false}
                onMouseEnter={() => setActiveIndex(filtered.length)}
                onClick={create}
                className={`flex cursor-pointer items-center gap-2 rounded-md px-3 py-2 ${
                  activeIndex === filtered.length ? 'bg-indigo-50 text-indigo-900' : 'text-gray-700'
                }`}
              >
                <svg className="size-4 text-indigo-600" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M10 4v12M4 10h12" strokeLinecap="round" />
                </svg>
                <span>
                  Create <span className="font-medium">&ldquo;{query.trim()}&rdquo;</span>
                </span>
              </li>
            )}
          </ul>
        </div>
      )}
    </div>
  )
}
