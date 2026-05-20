import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { createBrowserRouter, RouterProvider, Navigate } from 'react-router-dom'
import './index.css'
import { AuthProvider, useAuth } from './context/AuthContext'
import Layout from './layout/Layout'
import Login from './pages/Login'
import Profile from './pages/Profile'
import ColorLab from './pages/ColorLab'
import MiniLab from './pages/MiniLab'
import RouterError from './components/RouterError'

function HomeRedirect() {
  const { user } = useAuth()
  return <Navigate to={user ? '/color-lab' : '/login'} replace />
}

const router = createBrowserRouter([
  {
    path: '/',
    element: <Layout />,
    errorElement: <RouterError />,
    children: [
      { index: true, element: <HomeRedirect /> },
      { path: 'login', element: <Login /> },
      { path: 'profile', element: <Profile /> },
      { path: 'color-lab', element: <ColorLab /> },
      { path: 'mini-lab', element: <MiniLab /> },
    ]
  },
])

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <AuthProvider>
      <RouterProvider router={router} />
    </AuthProvider>
  </StrictMode>,
)
