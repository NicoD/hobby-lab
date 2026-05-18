import { useAuth } from '../context/AuthContext'

export default function Home() {
  const { user } = useAuth()

  return (
    <div className="flex flex-col items-center justify-center h-full py-24 gap-4">
      <h1 className="text-3xl font-semibold text-gray-800">Welcome to Recipe Lab</h1>
      {user ? (
        <p className="text-gray-500">You are signed in as <span className="font-medium text-gray-700">{user.email}</span>.</p>
      ) : (
        <p className="text-gray-500">Sign in to access your profile.</p>
      )}
    </div>
  )
}
