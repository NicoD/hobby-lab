import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import './index.css'
import { AuthProvider } from './context/AuthContext'
import Layout from './layout/Layout'
import Home from './pages/Home'
import Login from './pages/Login'
import Profile from './pages/Profile'

function wrap(page) {
  return <Layout>{page}</Layout>
}

const router = createBrowserRouter([
  { path: '/', element: wrap(<Home />) },
  { path: '/login', element: wrap(<Login />) },
  { path: '/profile', element: wrap(<Profile />) },
])

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <AuthProvider>
      <RouterProvider router={router} />
    </AuthProvider>
  </StrictMode>,
)
