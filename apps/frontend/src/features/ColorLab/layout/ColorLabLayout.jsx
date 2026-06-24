import { NavLink, Outlet } from 'react-router-dom'

const primaryLink = ({ isActive }) =>
  `block px-3 py-2 rounded-md text-sm font-medium transition-colors ${
    isActive
      ? 'bg-indigo-50 text-indigo-700'
      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
  }`

const catalogLink = ({ isActive }) =>
  `block px-3 py-1.5 rounded text-xs transition-colors ${
    isActive
      ? 'text-indigo-600 font-medium'
      : 'text-gray-400 hover:text-gray-600'
  }`

export default function ColorLabLayout() {
  return (
    <div className="flex h-[calc(100vh-3.5rem)]">
      <aside className="w-56 shrink-0 border-r border-gray-200 bg-white flex flex-col">
        <nav className="px-3 py-4 space-y-1">
          <NavLink to="/color-lab/paint" className={primaryLink}>
            Mes peintures
          </NavLink>
          <NavLink to="/color-lab/brush" className={primaryLink}>
            Mes pinceaux
          </NavLink>
        </nav>

        <nav className="px-3 py-3 mt-auto border-t border-gray-100 space-y-0.5">
          <p className="px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-gray-300">
            Catalogue
          </p>
          <NavLink to="/color-lab/catalog/brands" className={catalogLink}>
            Marques
          </NavLink>
          <NavLink to="/color-lab/catalog/colors" className={catalogLink}>
            Couleurs
          </NavLink>
          <NavLink to="/color-lab/catalog/references" className={catalogLink}>
            Références
          </NavLink>
        </nav>
      </aside>

      <main className="flex-1 overflow-y-auto bg-gray-50 p-6">
        <h1 className="text-2xl font-semibold text-gray-800 px-3 py-4 text-center">Color Lab</h1>
        <Outlet />
      </main>
    </div>
  )
}
