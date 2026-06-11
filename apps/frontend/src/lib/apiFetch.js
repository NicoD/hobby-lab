export async function apiFetch(url, { token, ...options } = {}) {
  const res = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  })
  if (!res.ok) {
    const body = await res.json().catch(() => ({}))
    const err = new Error(body.message ?? `HTTP ${res.status}`)
    err.status = res.status
    throw err
  }
  return res.status === 204 ? null : res.json()
}
