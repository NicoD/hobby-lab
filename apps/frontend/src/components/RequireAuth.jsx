import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

// Layout route guarding its children: anonymous users are sent to /login,
// keeping the requested location so Login can navigate back after sign-in.
export default function RequireAuth() {
  const { user } = useAuth()
  const location = useLocation()

  if (!user) return <Navigate to="/login" replace state={{ from: location }} />

  return <Outlet />
}
