import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import './index.css'
import App from './App.jsx'
import FooBarForm from './FooBar.jsx'

const router = createBrowserRouter([
  { path: '/', element: <App /> },
  { path: '/foobar', element: <FooBarForm /> },
])

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <RouterProvider router={router} />
  </StrictMode>,
)
