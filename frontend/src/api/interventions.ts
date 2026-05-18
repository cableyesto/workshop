import { useQuery, useMutation } from '@pinia/colada'
import { apiRequest } from './helpers'
import type {
  SearchInterventionResponse,
  UpdateInterventionPayload,
} from '../types/intervention'

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

// ============================================
// API FUNCTIONS
// ============================================

export async function updateInterventionAPI(
  interventionId: number,
  data: UpdateInterventionPayload,
): Promise<void> {
  return apiRequest<void>(
    `/api/interventions/${interventionId}`,
    {
      method: 'PATCH',
      body: JSON.stringify(data),
    },
    'Failed to update intervention',
  )
}

// ============================================
// MUTATIONS
// ============================================

export function useUpdateInterventionMutation(
  onSuccessCallback: () => void,
  onErrorCallback: (error: Error) => void,
) {
  return useMutation({
    key: ['update-intervention'],
    mutation: ({
      interventionId,
      data,
    }: {
      interventionId: number
      data: UpdateInterventionPayload
    }) => updateInterventionAPI(interventionId, data),
    onSuccess: () => {
      onSuccessCallback()
    },
    onError: (error) => {
      onErrorCallback(error)
    },
  })
}
