import { useQuery, useMutation, useQueryCache } from '@pinia/colada'
import { apiRequest } from './helpers'
import type { StoredCar, CarWithIntervention } from '../types/cars'
import type { Ref } from 'vue'

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

/**
 * Fetch all cars ready for restitution for the current garage
 */
export function useRestitutionCarsQuery() {
  return useQuery({
    key: ['cars', 'restitution'],
    query: () =>
      apiRequest<CarWithIntervention[]>(
        '/api/cars/restitution',
        {},
        'Failed to fetch restitution cars',
      ),
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}

// ============================================
// API FUNCTIONS
// ============================================

export async function updateClientCalledBackAPI(
  clientId: number,
  isCalledBack: boolean,
): Promise<void> {
  return apiRequest<void>(
    `/api/clients/${clientId}`,
    {
      method: 'PATCH',
      body: JSON.stringify({ isClientCalledBack: isCalledBack }),
    },
    'Failed to update client called back status',
  )
}

export async function removeCarFromStorageAPI(carId: number): Promise<void> {
  return apiRequest<void>(
    `/api/cars/${carId}/storage`,
    {
      method: 'PATCH',
      body: JSON.stringify({ isStored: false }),
    },
    'Failed to remove car from storage',
  )
}

// ============================================
// MUTATIONS
// ============================================

export function usePatchClientCalledBackMutation(updatingIds: Ref<Set<number>>) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['patch-client-called-back'],
    mutation: ({ clientId, value }: { clientId: number; value: boolean }) =>
      updateClientCalledBackAPI(clientId, value),
    onMutate: ({ clientId }) => {
      updatingIds.value.add(clientId)
    },
    onSuccess: () => {
      queryCache.invalidateQueries({ key: ['cars', 'restitution'], exact: true })
    },
    onError: (error) => {
      console.error('Error updating client called back:', error)
      alert(`Erreur: ${error.message}`)
    },
    onSettled: (_, __, { clientId }) => {
      updatingIds.value.delete(clientId)
    },
  })
}

export function useRemoveCarFromStorageMutation() {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['remove-car-from-storage'],
    mutation: removeCarFromStorageAPI,
    onSuccess: () => {
      queryCache.invalidateQueries({ key: ['cars', 'restitution'], exact: true })
    },
    onError: (error) => {
      console.error('Error removing car from storage:', error)
      alert(`Erreur: ${error.message}`)
    },
  })
}
