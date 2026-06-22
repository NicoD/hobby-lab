import { Navigate, useNavigate } from 'react-router-dom'
import { useAuth } from '../../../../shared/context/AuthContext'

export default function Profile() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  if (!user) return <Navigate to="/login" replace />

  function handleLogout() {
    logout()
    navigate('/', { replace: true })
  }

  return (
    <div className="flex items-center justify-center py-24 px-4">
      <div className="w-full max-w-sm bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <h1 className="text-xl font-semibold text-gray-900 mb-6">Profile</h1>

        <dl className="flex flex-col gap-4 text-sm">
          <div>
            <dt className="text-gray-500 font-medium">Email</dt>
            <dd className="text-gray-900 mt-0.5">{user.email}</dd>
          </div>
          <div>
            <dt className="text-gray-500 font-medium">User ID</dt>
            <dd className="text-gray-700 mt-0.5 font-mono text-xs break-all">{user.sub}</dd>
          </div>
          <div>
            <dt className="text-gray-500 font-medium">Roles</dt>
            <dd className="mt-1 flex gap-1 flex-wrap">
              {user.roles?.map((role) => (
                <span key={role} className="bg-indigo-50 text-indigo-700 text-xs font-medium px-2 py-0.5 rounded-full border border-indigo-100">
                  {role}
                </span>
              ))}
            </dd>
          </div>
        </dl>

        <button
          type="button"
          onClick={handleLogout}
          className="mt-8 w-full border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg px-4 py-2 transition-colors cursor-pointer"
        >
          Sign out
        </button>
      </div>
    </div>
  )
}
