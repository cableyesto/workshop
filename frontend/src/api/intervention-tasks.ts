import { useQuery, useMutation, useQueryCache } from '@pinia/colada'
import { apiRequest } from './helpers'
import type { Task, TaskPayload } from '../types/task'

// ============================================
// QUERIES
// ============================================

/**
 * Fetch intervention tasks by intervention ID (cached)
 */
export function useInterventionTasksQuery(interventionId: number | undefined, enabled: boolean) {
  return useQuery({
    key: ['interventions', interventionId ?? 0, 'tasks'],
    query: () => getInterventionTasksAPI(interventionId!),
    enabled: enabled && !!interventionId,
    staleTime: 5 * 60 * 1000, // 5 minutes cache
  })
}

// ============================================
// API FUNCTIONS
// ============================================

export async function getInterventionTasksAPI(interventionId: number): Promise<Task[]> {
  return apiRequest<Task[]>(
    `/api/interventions/${interventionId}/tasks`,
    {},
    'Failed to fetch intervention tasks',
  )
}

export async function createInterventionTaskAPI(
  interventionId: number,
  data: TaskPayload,
): Promise<Task> {
  return apiRequest<Task>(
    `/api/interventions/${interventionId}/tasks`,
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    },
    'Failed to create task',
  )
}

export async function updateInterventionTaskAPI(
  interventionId: number,
  serviceTaskId: number,
  data: TaskPayload,
): Promise<Task> {
  return apiRequest<Task>(
    `/api/interventions/${interventionId}/tasks/${serviceTaskId}`,
    {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    },
    'Failed to update task',
  )
}

// ============================================
// MUTATIONS
// ============================================

export function useCreateInterventionTaskMutation(
  onSuccessCallback: (task: Task) => void,
  onErrorCallback: (error: Error) => void,
) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['create-intervention-task'],
    mutation: ({ interventionId, data }: { interventionId: number; data: TaskPayload }) =>
      createInterventionTaskAPI(interventionId, data),
    onSuccess: (task) => {
      // Invalidate tasks cache to refresh the list
      // queryCache.invalidateQueries({ key: ['interventions'] })
      void queryCache.invalidateQueries({ key: ['interventions'] })
      onSuccessCallback(task)
    },
    onError: (error) => {
      onErrorCallback(error)
    },
  })
}

export function useUpdateInterventionTaskMutation(
  onSuccessCallback: (task: Task) => void,
  onErrorCallback: (error: Error) => void,
) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['update-intervention-task'],
    mutation: ({
      interventionId,
      serviceTaskId,
      data,
    }: {
      interventionId: number
      serviceTaskId: number
      data: TaskPayload
    }) => updateInterventionTaskAPI(interventionId, serviceTaskId, data),
    onSuccess: (task) => {
      // Invalidate tasks cache to refresh the list
      // queryCache.invalidateQueries({ key: ['interventions'] })
      void queryCache.invalidateQueries({ key: ['interventions'] })
      onSuccessCallback(task)
    },
    onError: (error) => {
      onErrorCallback(error)
    },
  })
}
