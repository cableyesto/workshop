import { useQuery } from '@pinia/colada'
import { apiRequest } from './helpers'
import type { StoredCar, CarWithIntervention } from '../types/cars'

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

/**
 * Fetch all cars with interventions for the current garage
 */
export function useInterventionCarsQuery() {
  return useQuery({
    key: ['cars', 'interventions'],
    query: () =>
      apiRequest<CarWithIntervention[]>(
        '/api/cars/interventions',
        {},
        'Failed to fetch intervention cars',
      ),
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}
