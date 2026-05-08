import { useQuery, useMutation, useQueryCache } from '@pinia/colada'
import { getToken } from '../utils/auth'
import type { Mechanic, Receptionist, EmployeeFormData } from '../types/employee'
import type { Ref } from 'vue'

/**
 * Fetch all mechanics for the current garage
 */
export function useMechanicsQuery() {
  return useQuery({
    key: ['mechanics'],
    query: async (): Promise<Mechanic[]> => {
      const token = getToken()
      const response = await fetch('/api/mechanics', {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      })

      if (!response.ok) {
        const error = await response.json().catch(() => ({ error: 'Failed to fetch mechanics' }))
        throw new Error(error.error || 'Failed to fetch mechanics')
      }

      return response.json()
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}

/**
 * Fetch all receptionists for the current garage
 */
export function useReceptionistsQuery() {
  return useQuery({
    key: ['receptionists'],
    query: async (): Promise<Receptionist[]> => {
      const token = getToken()
      const response = await fetch('/api/receptionists', {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      })

      if (!response.ok) {
        const error = await response
          .json()
          .catch(() => ({ error: 'Failed to fetch receptionists' }))
        throw new Error(error.error || 'Failed to fetch receptionists')
      }

      return response.json()
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}

/**
 * API function to create a new mechanic
 */
export async function createMechanicAPI(data: EmployeeFormData): Promise<Mechanic> {
  const token = getToken()
  const response = await fetch('/api/mechanics', {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Failed to create mechanic' }))
    throw new Error(error.error || 'Failed to create mechanic')
  }

  return response.json()
}

/**
 * API function to update a mechanic
 */
export async function updateMechanicAPI(
  id: number,
  data: Partial<EmployeeFormData>,
): Promise<Mechanic> {
  const token = getToken()
  const response = await fetch(`/api/mechanics/${id}`, {
    method: 'PUT',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Failed to update mechanic' }))
    throw new Error(error.error || 'Failed to update mechanic')
  }

  return response.json()
}

/**
 * API function to create a new receptionist
 */
export async function createReceptionistAPI(data: EmployeeFormData): Promise<Receptionist> {
  const token = getToken()
  const response = await fetch('/api/receptionists', {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Failed to create receptionist' }))
    throw new Error(error.error || 'Failed to create receptionist')
  }

  return response.json()
}

/**
 * API function to update a receptionist
 */
export async function updateReceptionistAPI(
  id: number,
  data: Partial<EmployeeFormData>,
): Promise<Receptionist> {
  const token = getToken()
  const response = await fetch(`/api/receptionists/${id}`, {
    method: 'PUT',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })

  if (!response.ok) {
    const error = await response
      .json()
      .catch(() => ({ error: 'Failed to update receptionist' }))
    throw new Error(error.error || 'Failed to update receptionist')
  }

  return response.json()
}

/**
 * Mutation hook to create a receptionist
 */
export function useCreateReceptionistMutation(dialogOpen: Ref<boolean>) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['create-receptionist'],
    mutation: createReceptionistAPI,
    onSuccess: () => {
      dialogOpen.value = false
      queryCache.invalidateQueries({
        key: ['receptionists'],
        exact: true,
      })
    },
    onError: (error) => {
      console.error('Error creating receptionist:', error)
      alert(`Erreur: ${error.message}`)
    },
  })
}

/**
 * Mutation hook to update a receptionist
 */
export function useUpdateReceptionistMutation(dialogOpen: Ref<boolean>) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['update-receptionist'],
    mutation: ({ id, data }: { id: number; data: Partial<EmployeeFormData> }) =>
      updateReceptionistAPI(id, data),
    onSuccess: () => {
      dialogOpen.value = false
      queryCache.invalidateQueries({
        key: ['receptionists'],
        exact: true,
      })
    },
    onError: (error) => {
      console.error('Error updating receptionist:', error)
      alert(`Erreur: ${error.message}`)
    },
  })
}

/**
 * Mutation hook to delete a receptionist
 */
export function useDeleteReceptionistMutation(dialogOpen: Ref<boolean>) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['delete-receptionist'],
    mutation: deleteReceptionistAPI,
    onSuccess: () => {
      dialogOpen.value = false
      queryCache.invalidateQueries({
        key: ['receptionists'],
        exact: true,
      })
    },
    onError: (error) => {
      console.error('Error deleting receptionist:', error)
      alert(`Erreur: ${error.message}`)
    },
  })
}

/**
 * Mutation hook to create a mechanic
 */
export function useCreateMechanicMutation(dialogOpen: Ref<boolean>) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['create-mechanic'],
    mutation: createMechanicAPI,
    onSuccess: () => {
      dialogOpen.value = false
      queryCache.invalidateQueries({
        key: ['mechanics'],
        exact: true,
      })
    },
    onError: (error) => {
      console.error('Error creating mechanic:', error)
      //TODO improve
      alert(`Erreur: ${error.message}`)
    },
  })
}

/**
 * Mutation hook to update a mechanic
 */
export function useUpdateMechanicMutation(dialogOpen: Ref<boolean>) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['update-mechanic'],
    mutation: ({ id, data }: { id: number; data: Partial<EmployeeFormData> }) =>
      updateMechanicAPI(id, data),
    onSuccess: () => {
      dialogOpen.value = false
      queryCache.invalidateQueries({
        key: ['mechanics'],
        exact: true,
      })
    },
    onError: (error) => {
      console.error('Error updating mechanic:', error)
      //TODO improve
      alert(`Erreur: ${error.message}`)
    },
  })
}

/**
 * API function to delete a receptionist
 */
export async function deleteReceptionistAPI(id: number): Promise<void> {
  const token = getToken()
  const response = await fetch(`/api/receptionists/${id}`, {
    method: 'DELETE',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  })

  if (!response.ok) {
    const error = await response
      .json()
      .catch(() => ({ error: 'Failed to delete receptionist' }))
    throw new Error(error.error || 'Failed to delete receptionist')
  }
}

/**
 * API function to delete a mechanic
 */
export async function deleteMechanicAPI(id: number): Promise<void> {
  const token = getToken()
  const response = await fetch(`/api/mechanics/${id}`, {
    method: 'DELETE',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Failed to delete mechanic' }))
    throw new Error(error.error || 'Failed to delete mechanic')
  }
}

/**
 * Mutation hook to delete a mechanic
 */
export function useDeleteMechanicMutation(dialogOpen: Ref<boolean>) {
  const queryCache = useQueryCache()

  return useMutation({
    key: ['delete-mechanic'],
    mutation: deleteMechanicAPI,
    onSuccess: () => {
      dialogOpen.value = false
      queryCache.invalidateQueries({
        key: ['mechanics'],
        exact: true,
      })
    },
    onError: (error) => {
      console.error('Error deleting mechanic:', error)
      //TODO improve
      alert(`Erreur: ${error.message}`)
    },
  })
}
