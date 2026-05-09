import { useMutation, useQueryCache } from '@pinia/colada'
import { apiRequest } from './helpers'
import type { UpdateClientData } from '../types/client'

// ============================================
// QUERIES
// ============================================

// ============================================
// API FUNCTIONS
// ============================================

async function updateClientAPI(clientId: number, data: UpdateClientData): Promise<void> {
  return apiRequest<void>(
    `/api/clients/${clientId}`,
    {
      method: 'PUT',
      body: JSON.stringify(data),
    },
    'Failed to update client',
  )
}

// ============================================
// MUTATIONS
// ============================================

export function useUpdateClientMutation(
  onSuccessCallback: () => void,
  onErrorCallback: (error: Error) => void,
) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['update-client'],
    mutation: ({ clientId, data }: { clientId: number; data: UpdateClientData }) =>
      updateClientAPI(clientId, data),
    onSuccess: () => {
      queryCache.invalidateQueries({ key: ['cars'] })
      onSuccessCallback()
    },
    onError: (error) => {
      onErrorCallback(error)
    },
  })
}
