import { Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

function UserCircleIcon({ className }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
  )
}

export default function Layout({ children }) {
  const { user } = useAuth()

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <header className="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between">
        <nav className="flex items-center gap-6">
          <Link to="/" className="font-semibold text-gray-900 tracking-tight">
            Recipe Lab
          </Link>
          <Link to="/" className="text-sm text-gray-500 hover:text-gray-900 transition-colors">
            Home
          </Link>
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
        {children}
      </main>
    </div>
  )
}
