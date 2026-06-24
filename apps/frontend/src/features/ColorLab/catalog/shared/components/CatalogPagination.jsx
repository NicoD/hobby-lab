export default function CatalogPagination({ page, total, limit, onPage }) {
  const pageCount = Math.ceil(total / limit)

  if (pageCount <= 1) return null

  const pages = buildPageRange(page, pageCount)

  return (
    <div className="flex items-center justify-between px-1 pt-4 border-t border-gray-100">
      <p className="text-xs text-gray-400">
        {(page - 1) * limit + 1}–{Math.min(page * limit, total)} sur {total}
      </p>

      <div className="flex items-center gap-1">
        <PageButton onClick={() => onPage(page - 1)} disabled={page === 1} label="←" />

        {pages.map((p, i) =>
          p === '…' ? (
            <span key={`ellipsis-${i}`} className="px-2 text-gray-300 text-sm select-none">…</span>
          ) : (
            <PageButton
              key={p}
              onClick={() => onPage(p)}
              active={p === page}
              label={p}
            />
          )
        )}

        <PageButton onClick={() => onPage(page + 1)} disabled={page === pageCount} label="→" />
      </div>
    </div>
  )
}

function PageButton({ onClick, disabled, active, label }) {
  return (
    <button
      onClick={onClick}
      disabled={disabled}
      className={`min-w-[2rem] h-8 px-2 rounded text-sm transition-colors ${
        active
          ? 'bg-indigo-600 text-white font-medium'
          : disabled
          ? 'text-gray-300 cursor-default'
          : 'text-gray-600 hover:bg-gray-100'
      }`}
    >
      {label}
    </button>
  )
}

function buildPageRange(current, total) {
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  if (current <= 4) return [1, 2, 3, 4, 5, '…', total]
  if (current >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total]
  return [1, '…', current - 1, current, current + 1, '…', total]
}
