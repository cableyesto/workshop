import { useQuery } from '@pinia/colada'
import type { Garage } from '@/types'

/**
 * Fetch garage by ID with time slots
 */
export function useGarageQuery(id: number) {
  return useQuery({
    key: ['garage', id],
    query: async (): Promise<Garage> => {
      const token = localStorage.getItem('jwt_token')
      const response = await fetch(`/api/garages/${id}`, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      })

      if (!response.ok) {
        const error = await response.json().catch(() => ({ error: 'Failed to fetch garage' }))
        throw new Error(error.error || 'Failed to fetch garage')
      }

      return response.json()
    },
    staleTime: Infinity,
    refetchOnWindowFocus: false,
  })
}
