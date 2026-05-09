import { useQuery } from '@pinia/colada'
import { apiRequest, createMutationHook } from './helpers'
import type { Mechanic, Receptionist, EmployeeFormData } from '../types/employee'
import type { Ref } from 'vue'

// ============================================
// QUERIES
// ============================================

/**
 * Fetch all mechanics for the current garage
 */
export function useMechanicsQuery() {
  return useQuery({
    key: ['mechanics'],
    query: () => apiRequest<Mechanic[]>('/api/mechanics', {}, 'Failed to fetch mechanics'),
    staleTime: 5 * 60 * 1000,
  })
}

/**
 * Fetch all receptionists for the current garage
 */
export function useReceptionistsQuery() {
  return useQuery({
    key: ['receptionists'],
    query: () =>
      apiRequest<Receptionist[]>('/api/receptionists', {}, 'Failed to fetch receptionists'),
    staleTime: 5 * 60 * 1000,
  })
}

// ============================================
// MECHANIC API FUNCTIONS
// ============================================

export async function createMechanicAPI(data: EmployeeFormData): Promise<Mechanic> {
  return apiRequest<Mechanic>(
    '/api/mechanics',
    { method: 'POST', body: JSON.stringify(data) },
    'Failed to create mechanic',
  )
}

export async function updateMechanicAPI(
  id: number,
  data: Partial<EmployeeFormData>,
): Promise<Mechanic> {
  return apiRequest<Mechanic>(
    `/api/mechanics/${id}`,
    { method: 'PUT', body: JSON.stringify(data) },
    'Failed to update mechanic',
  )
}

export async function deleteMechanicAPI(id: number): Promise<void> {
  return apiRequest<void>(
    `/api/mechanics/${id}`,
    { method: 'DELETE' },
    'Failed to delete mechanic',
  )
}

// ============================================
// RECEPTIONIST API FUNCTIONS
// ============================================

export async function createReceptionistAPI(data: EmployeeFormData): Promise<Receptionist> {
  return apiRequest<Receptionist>(
    '/api/receptionists',
    { method: 'POST', body: JSON.stringify(data) },
    'Failed to create receptionist',
  )
}

export async function updateReceptionistAPI(
  id: number,
  data: Partial<EmployeeFormData>,
): Promise<Receptionist> {
  return apiRequest<Receptionist>(
    `/api/receptionists/${id}`,
    { method: 'PUT', body: JSON.stringify(data) },
    'Failed to update receptionist',
  )
}

export async function deleteReceptionistAPI(id: number): Promise<void> {
  return apiRequest<void>(
    `/api/receptionists/${id}`,
    { method: 'DELETE' },
    'Failed to delete receptionist',
  )
}

// ============================================
// MECHANIC MUTATIONS
// ============================================

export function useCreateMechanicMutation(dialogOpen: Ref<boolean>) {
  return createMutationHook(
    'create-mechanic',
    createMechanicAPI,
    'mechanics',
    dialogOpen,
    'Error creating mechanic',
  )
}

export function useUpdateMechanicMutation(dialogOpen: Ref<boolean>) {
  return createMutationHook(
    'update-mechanic',
    ({ id, data }: { id: number; data: Partial<EmployeeFormData> }) =>
      updateMechanicAPI(id, data),
    'mechanics',
    dialogOpen,
    'Error updating mechanic',
  )
}

export function useDeleteMechanicMutation(dialogOpen: Ref<boolean>) {
  return createMutationHook(
    'delete-mechanic',
    deleteMechanicAPI,
    'mechanics',
    dialogOpen,
    'Error deleting mechanic',
  )
}

// ============================================
// RECEPTIONIST MUTATIONS
// ============================================

export function useCreateReceptionistMutation(dialogOpen: Ref<boolean>) {
  return createMutationHook(
    'create-receptionist',
    createReceptionistAPI,
    'receptionists',
    dialogOpen,
    'Error creating receptionist',
  )
}

export function useUpdateReceptionistMutation(dialogOpen: Ref<boolean>) {
  return createMutationHook(
    'update-receptionist',
    ({ id, data }: { id: number; data: Partial<EmployeeFormData> }) =>
      updateReceptionistAPI(id, data),
    'receptionists',
    dialogOpen,
    'Error updating receptionist',
  )
}

export function useDeleteReceptionistMutation(dialogOpen: Ref<boolean>) {
  return createMutationHook(
    'delete-receptionist',
    deleteReceptionistAPI,
    'receptionists',
    dialogOpen,
    'Error deleting receptionist',
  )
}
