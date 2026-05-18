import { useQuery, useMutation } from '@pinia/colada'
import { apiRequest } from './helpers'
import type {
  Intervention,
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

/**
 * Fetch intervention by ID (cached across steps)
 */
export function useInterventionQuery(interventionId: number | undefined, enabled: boolean) {
  return useQuery({
    key: ['interventions', interventionId ?? 0],
    query: () => getInterventionAPI(interventionId!),
    enabled: enabled && !!interventionId,
    staleTime: 5 * 60 * 1000, // 5 minutes cache
  })
}

// ============================================
// API FUNCTIONS
// ============================================

export async function getInterventionAPI(interventionId: number): Promise<Intervention> {
  return apiRequest<Intervention>(
    `/api/interventions/${interventionId}`,
    {},
    'Failed to fetch intervention',
  )
}

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
