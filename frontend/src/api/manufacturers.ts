import { useQuery } from '@pinia/colada'
import { apiRequest } from './helpers'
import type { Manufacturer } from '../types/manufacturer'

/**
 * Fetch all manufacturers
 */
export function useManufacturersQuery() {
  return useQuery({
    key: ['manufacturers'],
    query: () =>
      apiRequest<Manufacturer[]>('/api/manufacturers', {}, 'Failed to fetch manufacturers'),
    staleTime: 60 * 60 * 1000, // 1 hour - manufacturers rarely change
  })
}
