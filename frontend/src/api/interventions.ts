import { useQuery } from '@pinia/colada'
import { apiRequest } from './helpers'
import type { SearchInterventionResponse } from '../types/intervention'

// ============================================
// QUERIES
// ============================================

/**
 * Search for active intervention by mechanic and license plate
 */
export function useSearchInterventionQuery(mechanicId: number, licensePlate: string) {
  return useQuery({
    key: ['interventions', 'search', mechanicId, licensePlate],
    query: () =>
      apiRequest<SearchInterventionResponse>(
        `/api/interventions/search?mechanicId=${mechanicId}&licensePlate=${licensePlate}`,
        {},
        'Failed to search intervention',
      ),
    enabled: false, // Manual trigger
  })
}
