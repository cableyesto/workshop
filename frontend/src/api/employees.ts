import { useQuery } from '@pinia/colada'
import { getToken } from '../utils/auth'
import type { Mechanic, Receptionist, EmployeeFormData } from '../types/employee'

/**
 * Fetch all mechanics for the current garage
 */
export function useMechanicsQuery() {
  return useQuery({
    key: ['mechanics'],
    query: async (): Promise<Mechanic[]> => {
      const token = getToken()
      const response = await fetch('/api/mechanics', {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      })

      if (!response.ok) {
        const error = await response.json().catch(() => ({ error: 'Failed to fetch mechanics' }))
        throw new Error(error.error || 'Failed to fetch mechanics')
      }

      return response.json()
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}

/**
 * Fetch all receptionists for the current garage
 */
export function useReceptionistsQuery() {
  return useQuery({
    key: ['receptionists'],
    query: async (): Promise<Receptionist[]> => {
      const token = getToken()
      const response = await fetch('/api/receptionists', {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      })

      if (!response.ok) {
        const error = await response
          .json()
          .catch(() => ({ error: 'Failed to fetch receptionists' }))
        throw new Error(error.error || 'Failed to fetch receptionists')
      }

      return response.json()
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}

/**
 * API function to create a new mechanic
 */
export async function createMechanicAPI(data: EmployeeFormData): Promise<Mechanic> {
  const token = getToken()
  const response = await fetch('/api/mechanics', {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Failed to create mechanic' }))
    throw new Error(error.error || 'Failed to create mechanic')
  }

  return response.json()
}
