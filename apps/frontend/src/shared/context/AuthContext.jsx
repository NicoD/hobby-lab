import { createContext, useContext, useState, useCallback, useEffect, useRef } from 'react'

const AuthContext = createContext(null)

// The refresh cookie is httpOnly, so JS can't tell whether a session exists.
// This flag only mirrors "a session probably exists" to skip the silent
// refresh (and its guaranteed 401) when the user is logged out.
const SESSION_FLAG = 'auth:hasSession'

function decodeJwt(token) {
  try {
    // JWT payloads are base64url-encoded; atob only accepts standard base64
    const payload = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')
    return JSON.parse(atob(payload))
  } catch {
    return null
  }
}

export function AuthProvider({ children }) {
  const [token, setToken] = useState(null)
  const [ready, setReady] = useState(() => !localStorage.getItem(SESSION_FLAG))
  const refreshPromiseRef = useRef(null)

  const user = token ? decodeJwt(token) : null

  const login = useCallback(async (email, password) => {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })
    if (!res.ok) {
      const body = await res.json().catch(() => ({}))
      throw new Error(body.message ?? 'Invalid credentials')
    }
    const { accessToken } = await res.json()
    localStorage.setItem(SESSION_FLAG, '1')
    setToken(accessToken)
  }, [])

  const logout = useCallback(async () => {
    localStorage.removeItem(SESSION_FLAG)
    setToken(null)
    await fetch('/api/auth/logout', { method: 'POST', credentials: 'include' }).catch(() => {})
  }, [])

  const refresh = useCallback(async () => {
    if (refreshPromiseRef.current) return refreshPromiseRef.current

    refreshPromiseRef.current = fetch('/api/auth/refresh', { method: 'POST', credentials: 'include' })
      .then(res => {
        if (!res.ok) throw new Error('refresh_failed')
        return res.json()
      })
      .then(data => {
        localStorage.setItem(SESSION_FLAG, '1')
        setToken(data.accessToken)
        return data.accessToken
      })
      .catch(err => {
        localStorage.removeItem(SESSION_FLAG)
        throw err
      })
      .finally(() => { refreshPromiseRef.current = null })

    return refreshPromiseRef.current
  }, [])

  // On mount, silently try to restore a session via the httpOnly cookie.
  // Goes through refresh() so the in-flight promise is shared: StrictMode's
  // double-invoked effect must not send two refresh calls — token rotation
  // would treat the second one as a stolen-token replay.
  useEffect(() => {
    if (!localStorage.getItem(SESSION_FLAG)) return
    refresh().catch(() => {}).finally(() => setReady(true))
  }, [refresh])

  if (!ready) return null

  return (
    <AuthContext.Provider value={{ user, token, login, logout, refresh }}>
      {children}
    </AuthContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth() {
  return useContext(AuthContext)
}
