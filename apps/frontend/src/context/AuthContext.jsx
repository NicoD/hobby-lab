import { createContext, useContext, useState, useCallback } from 'react'

const AuthContext = createContext(null)

function decodeJwt(token) {
  try {
    const payload = token.split('.')[1]
    return JSON.parse(atob(payload))
  } catch {
    return null
  }
}

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem('accessToken'))

  const user = token ? decodeJwt(token) : null

  const login = useCallback(async (email, password) => {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })
    if (!res.ok) {
      const body = await res.json().catch(() => ({}))
      throw new Error(body.message ?? 'Invalid credentials')
    }
    const { accessToken } = await res.json()
    localStorage.setItem('accessToken', accessToken)
    setToken(accessToken)
  }, [])

  const logout = useCallback(() => {
    localStorage.removeItem('accessToken')
    setToken(null)
  }, [])

  return (
    <AuthContext.Provider value={{ user, token, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  return useContext(AuthContext)
}
