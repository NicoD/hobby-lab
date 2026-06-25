import { useCallback } from 'react'
import { useAuth } from '../context/AuthContext'
import { apiFetch, ApiFetchOptions, HttpError } from '../lib/apiFetch'

export function useApiFetch<T>() {
  const { token, refresh, logout } = useAuth()

  return useCallback(async (url: string, options: Omit<ApiFetchOptions, 'token'> = {}) => {
    try {
      return await apiFetch<T>(url, { token: token ?? undefined, ...options })
    } catch (err: unknown) {
      if (!(err instanceof HttpError) || err.status !== 401) throw err
      try {
        const newToken = await refresh()
        return await apiFetch<T>(url, { token: newToken, ...options })
      } catch {
        void logout()
        throw err
      }
    }
  }, [token, refresh, logout])
}
