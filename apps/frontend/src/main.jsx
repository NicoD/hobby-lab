import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { createBrowserRouter, RouterProvider, Navigate } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import './index.css'
import { AuthProvider, useAuth } from './context/AuthContext'
import Layout from './layout/Layout'
import Login from './pages/Login'
import Profile from './pages/Profile'
import ColorLabLayout from './layout/ColorLabLayout'
import MiniLab from './pages/MiniLab'
import RouterError from './components/RouterError'
import ColorLabPaint from './pages/ColorLab/Paint'

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
      {
        path: 'color-lab',
        element: <ColorLabLayout />,
        children: [
          { index: true, element: <Navigate to="paint" replace /> },
          { path: 'paint', element: <ColorLabPaint /> },
        ]
      },
      { path: 'mini-lab', element: <MiniLab /> },
    ]
  },
])

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5,
      retry: (failureCount, error) => error.status >= 500 && failureCount < 3,
    },
    mutations: {
      retry: false,
    },
  },
})

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <RouterProvider router={router} />
      </AuthProvider>
    </QueryClientProvider>
  </StrictMode>,
)
