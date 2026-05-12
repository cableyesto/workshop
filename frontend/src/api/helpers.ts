import { useMutation, useQueryCache } from '@pinia/colada'
import { getToken } from '../utils/auth'
import type { Ref } from 'vue'

/**
 * Generic authenticated API request helper
 */
export async function apiRequest<T>(
  url: string,
  options: RequestInit = {},
  errorMessage = 'Request failed',
): Promise<T> {
  const token = getToken()
  const response = await fetch(url, {
    ...options,
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: errorMessage }))
    throw new Error(error.error || errorMessage)
  }

  // DELETE returns 204 No Content
  if (response.status === 204) {
    return undefined as T
  }

  return response.json()
}

/**
 * Generic mutation hook factory
 */
export function createMutationHook<TData = void, TVariables = unknown>(
  key: string,
  mutationFn: (variables: TVariables) => Promise<TData>,
  cacheKey: string,
  dialogOpen: Ref<boolean>,
  errorPrefix: string,
) {
  const queryCache = useQueryCache()

  return useMutation({
    key: [key],
    mutation: mutationFn,
    onSuccess: () => {
      dialogOpen.value = false
      queryCache.invalidateQueries({
        key: [cacheKey],
        exact: true,
      })
    },
    onError: (error) => {
      console.error(`${errorPrefix}:`, error)
    },
  })
}
