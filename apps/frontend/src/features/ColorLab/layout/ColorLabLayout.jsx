import { NavLink, Outlet } from 'react-router-dom'

export default function ColorLabLayout() {
  return (
    <div className="flex h-[calc(100vh-3.5rem)]">
      <aside className="w-56 shrink-0 border-r border-gray-200 bg-white">
        <nav className="px-3 py-4 space-y-1">
          <NavLink
            to="/color-lab/paint"
            className={({ isActive }) =>
              `block px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                isActive
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
              }`
            }
          >
            Peintures
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
