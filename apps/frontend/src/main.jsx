import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { createBrowserRouter, RouterProvider, Navigate } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import './index.css'
import { AuthProvider, useAuth } from './shared/context/AuthContext'
import Layout from './layout/Layout'
import Login from './features/User/login/pages/Login'
import Profile from './features/User/profile/pages/Profile'
import ColorLabLayout from './features/ColorLab/layout/ColorLabLayout'
import MiniLab from './features/MiniLab/pages/MiniLab'
import RouterError from './shared/components/RouterError'
import RequireAuth from './shared/components/RequireAuth'
import StashPaints from './features/ColorLab/stash/paints/pages/StashPaints'
import ColorLabCatalogBrands from './features/ColorLab/catalog/brands/pages/Brands'
import ColorLabCatalogColors from './features/ColorLab/catalog/colors/pages/Colors'
import CatalogPaints from './features/ColorLab/catalog/paints/pages/CatalogPaints'

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
      {
        element: <RequireAuth />,
        children: [
          { path: 'profile', element: <Profile /> },
          {
            path: 'color-lab',
            element: <ColorLabLayout />,
            children: [
              { index: true, element: <Navigate to="stash/paints" replace /> },
              { path: 'stash/paints', element: <StashPaints /> },
              { path: 'catalog/brands', element: <ColorLabCatalogBrands /> },
              { path: 'catalog/colors', element: <ColorLabCatalogColors /> },
              { path: 'catalog/paints', element: <CatalogPaints /> },
            ]
          },
          { path: 'mini-lab', element: <MiniLab /> },
        ]
      },
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
