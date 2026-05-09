import { useQuery } from '@pinia/colada'
import { apiRequest } from './helpers'
import type { StoredCar } from '../types/cars'

// ============================================
// QUERIES
// ============================================

/**
 * Fetch all stored cars for the current garage
 */
export function useStoredCarsQuery() {
  return useQuery({
    key: ['cars', 'storage'],
    query: () => apiRequest<StoredCar[]>('/api/cars/storage', {}, 'Failed to fetch stored cars'),
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}
