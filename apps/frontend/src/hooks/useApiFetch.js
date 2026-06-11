import { useCallback } from 'react'
import { useAuth } from '../context/AuthContext'
import { apiFetch } from '../lib/apiFetch'

export function useApiFetch() {
  const { token, logout } = useAuth()

  return useCallback(async (url, options = {}) => {
    try {
      return await apiFetch(url, { token, ...options })
    } catch (err) {
      if (err.status === 401) logout()
      throw err
    }
  }, [token, logout])
}
