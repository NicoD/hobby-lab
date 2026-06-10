import { Link, Outlet } from 'react-router-dom'

export default function ColorLab() {
  return (
    <div className="flex flex-col items-center justify-center h-full py-24 gap-4">
      <h1 className="text-3xl font-semibold text-gray-800">Color Lab</h1>
      <div className="menu">
        <Link to="/color-lab/paint" title="Peintures" className="text-indigo-600 hover:text-indigo-800 transition-colors">
          Peintures
        </Link>
        </div>
      <div>
        <Outlet />
      </div>
    </div>
  )
}
