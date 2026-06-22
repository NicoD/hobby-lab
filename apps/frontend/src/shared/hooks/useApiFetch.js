import { useCallback } from 'react'
import { useAuth } from '../context/AuthContext'
import { apiFetch } from '../lib/apiFetch'

export function useApiFetch() {
  const { token, refresh, logout } = useAuth()

  return useCallback(async (url, options = {}) => {
    try {
      return await apiFetch(url, { token, ...options })
    } catch (err) {
      if (err.status !== 401) throw err

      try {
        const newToken = await refresh()
        return await apiFetch(url, { token: newToken, ...options })
      } catch {
        logout()
        throw err
      }
    }
  }, [token, refresh, logout])
}
