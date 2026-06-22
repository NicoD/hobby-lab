import { Link, NavLink, Outlet } from 'react-router-dom'
import { ErrorBoundary } from 'react-error-boundary'
import { useAuth } from '../shared/context/AuthContext'

function UserCircleIcon({ className }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
  )
}

function ColorLabCircleIcon({ className }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      <g transform="rotate(135, 12, 12)">
        <rect x="10" y="4.5" width="4" height="3.5" fill="currentColor" stroke="none" />
        <path fill="currentColor" stroke="none" d="M12 8 C7.5 9.5 7 14.5 12 20 C17 14.5 16.5 9.5 12 8 Z" />
      </g>
    </svg>
  )
}

function MiniLabCircleIcon({ className }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      <circle cx="12" cy="7" r="2.2" fill="currentColor" stroke="none" />
      <rect x="10" y="9.8" width="4" height="5" rx="0.5" fill="currentColor" stroke="none" />
      <rect x="7.5" y="10.5" width="9" height="2" rx="0.5" fill="currentColor" stroke="none" />
      <rect x="10" y="14.8" width="4" height="1.5" rx="0.5" fill="currentColor" stroke="none" />
      <rect x="10" y="16.3" width="1.7" height="4" rx="0.5" fill="currentColor" stroke="none" />
      <rect x="12.3" y="16.3" width="1.7" height="4" rx="0.5" fill="currentColor" stroke="none" />
    </svg>
  )
}

export default function Layout() {
  const { user } = useAuth()

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <header className="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between">
        <nav className="flex items-center gap-6">
          {user ? (
            <>
              <NavLink to="/color-lab" title="Color lab" className={({ isActive }) => isActive ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-700 transition-colors'}>
                <ColorLabCircleIcon className="w-7 h-7" />
              </NavLink>
              <NavLink to="/mini-lab" title="Mini lab" className={({ isActive }) => isActive ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-700 transition-colors'}>
                <MiniLabCircleIcon className="w-7 h-7" />
              </NavLink>
            </>) : <></>
          }
        </nav>

        <div>
          {user ? (
            <Link to="/profile" title={user.email} className="text-indigo-600 hover:text-indigo-800 transition-colors">
              <UserCircleIcon className="w-7 h-7" />
            </Link>
          ) : (
            <Link to="/login" title="Sign in" className="text-gray-400 hover:text-gray-700 transition-colors">
              <UserCircleIcon className="w-7 h-7" />
            </Link>
          )}
        </div>
      </header>

      <main className="flex-1">
        <ErrorBoundary fallback={<div>Quelque chose s'est cassé.</div>}>
          <Outlet />
        </ErrorBoundary>
      </main>
    </div>
  )
}
