import { Navigate } from 'react-router-dom'
import { useAuth } from '../shared/context/AuthContext'

export default function HomeRedirect() {
  const { user } = useAuth()
  return <Navigate to={user ? '/color-lab' : '/login'} replace />
}
