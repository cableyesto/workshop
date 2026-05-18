import { useQuery, useMutation, useQueryCache } from '@pinia/colada'
import { apiRequest } from './helpers'
import type {
  Intervention,
  SearchInterventionResponse,
  UpdateInterventionPayload,
} from '../types/intervention'
import type { Task } from '../types/task'

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

export async function searchInterventionAPI(
  mechanicId: number,
  licensePlate: string,
): Promise<SearchInterventionResponse> {
  return apiRequest<SearchInterventionResponse>(
    `/api/interventions/search?mechanicId=${mechanicId}&licensePlate=${licensePlate}`,
    {},
    'Failed to search intervention',
  )
}

export async function createInterventionAPI(
  mechanicId: number,
  licensePlate: string,
): Promise<{ id: number }> {
  return apiRequest<{ id: number }>(
    '/api/interventions',
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        mechanicId,
        licensePlate,
      }),
    },
    'Failed to create intervention',
  )
}

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

export async function getInterventionTasksAPI(interventionId: number): Promise<Task[]> {
  return apiRequest<Task[]>(
    `/api/interventions/${interventionId}/tasks`,
    {},
    'Failed to fetch intervention tasks',
  )
}

// ============================================
// MUTATIONS
// ============================================

export function useUpdateInterventionMutation(
  onSuccessCallback: () => void,
  onErrorCallback: (error: Error) => void,
) {
  const queryCache = useQueryCache()

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
      // Invalidate intervention cache to ensure fresh data
      queryCache.invalidateQueries({ key: ['interventions'] })
      onSuccessCallback()
    },
    onError: (error) => {
      onErrorCallback(error)
    },
  })
}
