export type ApiFetchOptions = RequestInit & { token?: string };

export class HttpError extends Error {
  readonly status: number;

  constructor(message: string, status: number) {
    super(message);
    this.status = status;
  }
}

export async function apiFetch<T>(
  url: string,
  { token, ...options }: ApiFetchOptions = {},
): Promise<T | null> {
  const res = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...Object.fromEntries(new Headers(options.headers).entries()),
    },
  });
  if (!res.ok) {
    const body = (await res.json().catch(() => ({}))) as { message?: string };
    throw new HttpError(body.message ?? `HTTP ${res.status.toString()}`, res.status);
  }
  return res.status === 204 ? null : (res.json() as Promise<T>);
}
