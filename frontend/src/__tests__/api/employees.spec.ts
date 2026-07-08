import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { ref } from 'vue'
import {
  useMechanicsQuery,
  useReceptionistsQuery,
  createMechanicAPI,
  updateMechanicAPI,
  deleteMechanicAPI,
  createReceptionistAPI,
  updateReceptionistAPI,
  deleteReceptionistAPI,
  useCreateMechanicMutation,
  useUpdateMechanicMutation,
  useDeleteMechanicMutation,
  useCreateReceptionistMutation,
  useUpdateReceptionistMutation,
  useDeleteReceptionistMutation,
} from '@/api/employees'
import * as helpers from '@/api/helpers'
import type { Mechanic, Receptionist, EmployeeFormData } from '@/types/employee'

// Mock dependencies
vi.mock('@/api/helpers', () => ({
  apiRequest: vi.fn(),
  createMutationHook: vi.fn(),
}))

vi.mock('@pinia/colada', () => ({
  useQuery: vi.fn((config) => ({
    data: { value: null },
    isLoading: { value: false },
    error: { value: null },
    config,
  })),
}))

describe('employees.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const mockMechanic: Mechanic = {
    id: 1,
    firstName: 'Jean',
    lastName: 'Dupont',
    birthDate: '1980-01-15',
    hireDate: '2020-01-01',
  }

  const mockMechanics: Mechanic[] = [
    mockMechanic,
    {
      id: 2,
      firstName: 'Marie',
      lastName: 'Martin',
      birthDate: '1985-05-20',
      hireDate: '2019-03-15',
    },
  ]

  const mockReceptionist: Receptionist = {
    id: 1,
    firstName: 'Sophie',
    lastName: 'Bernard',
    birthDate: '1990-06-10',
    hireDate: '2021-02-01',
    email: 'sophie.bernard@example.com',
  }

  const mockReceptionists: Receptionist[] = [
    mockReceptionist,
    {
      id: 2,
      firstName: 'Claire',
      lastName: 'Dubois',
      birthDate: '1988-03-25',
      hireDate: '2020-09-10',
      email: 'claire.dubois@example.com',
    },
  ]

  const mockEmployeeFormData: EmployeeFormData = {
    type: 'mechanic',
    firstName: 'Pierre',
    lastName: 'Durand',
    birthDate: '1992-08-15',
    hireDate: '2023-01-10',
    pin: '1234',
  }

  describe('useMechanicsQuery', () => {
    it('can be called without parameters', () => {
      expect(() => {
        useMechanicsQuery()
      }).not.toThrow()
    })

    it('returns query result object', () => {
      const result = useMechanicsQuery()

      expect(result).toBeDefined()
      expect(result).toHaveProperty('data')
      expect(result).toHaveProperty('isLoading')
      expect(result).toHaveProperty('error')
    })

    it('uses correct query key', () => {
      const result = useMechanicsQuery() as any

      expect(result.config.key).toEqual(['mechanics'])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useMechanicsQuery() as any

      const fiveMinutes = 5 * 60 * 1000
      expect(result.config.staleTime).toBe(fiveMinutes)
    })

    it('includes query function', () => {
      const result = useMechanicsQuery() as any

      expect(result.config.query).toBeDefined()
      expect(typeof result.config.query).toBe('function')
    })
  })

  describe('useReceptionistsQuery', () => {
    it('can be called without parameters', () => {
      expect(() => {
        useReceptionistsQuery()
      }).not.toThrow()
    })

    it('uses correct query key', () => {
      const result = useReceptionistsQuery() as any

      expect(result.config.key).toEqual(['receptionists'])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useReceptionistsQuery() as any

      const fiveMinutes = 5 * 60 * 1000
      expect(result.config.staleTime).toBe(fiveMinutes)
    })

    it('includes query function', () => {
      const result = useReceptionistsQuery() as any

      expect(result.config.query).toBeDefined()
      expect(typeof result.config.query).toBe('function')
    })
  })

  describe('createMechanicAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockMechanic)

      await createMechanicAPI(mockEmployeeFormData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/mechanics',
        expect.objectContaining({
          method: 'POST',
        }),
        'Failed to create mechanic',
      )
    })

    it('sends employee data as JSON in body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockMechanic)

      await createMechanicAPI(mockEmployeeFormData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(mockEmployeeFormData),
        }),
        expect.any(String),
      )
    })

    it('returns created Mechanic with ID', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockMechanic)

      const result = await createMechanicAPI(mockEmployeeFormData)

      expect(result).toEqual(mockMechanic)
      expect(result).toHaveProperty('id')
      expect(result.id).toBe(1)
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Create failed'))

      await expect(createMechanicAPI(mockEmployeeFormData)).rejects.toThrow('Create failed')
    })
  })

  describe('updateMechanicAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockMechanic)

      await updateMechanicAPI(123, { firstName: 'Updated' })

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/mechanics/123',
        expect.objectContaining({
          method: 'PUT',
        }),
        'Failed to update mechanic',
      )
    })

    it('sends partial data for updates', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockMechanic)

      const partialData: Partial<EmployeeFormData> = {
        firstName: 'NewName',
        lastName: 'NewLastName',
      }

      await updateMechanicAPI(1, partialData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(partialData),
        }),
        expect.any(String),
      )
    })

    it('returns updated Mechanic', async () => {
      const updatedMechanic = { ...mockMechanic, firstName: 'Updated' }
      vi.mocked(helpers.apiRequest).mockResolvedValue(updatedMechanic)

      const result = await updateMechanicAPI(1, { firstName: 'Updated' })

      expect(result).toEqual(updatedMechanic)
      expect(result.firstName).toBe('Updated')
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Update failed'))

      await expect(updateMechanicAPI(1, {})).rejects.toThrow('Update failed')
    })
  })

  describe('deleteMechanicAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await deleteMechanicAPI(123)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/mechanics/123',
        { method: 'DELETE' },
        'Failed to delete mechanic',
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await deleteMechanicAPI(1)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Delete failed'))

      await expect(deleteMechanicAPI(1)).rejects.toThrow('Delete failed')
    })
  })

  describe('createReceptionistAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockReceptionist)

      const receptionistData: EmployeeFormData = {
        ...mockEmployeeFormData,
        type: 'receptionist',
        email: 'test@example.com',
        password: 'password123',
      }

      await createReceptionistAPI(receptionistData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/receptionists',
        expect.objectContaining({
          method: 'POST',
        }),
        'Failed to create receptionist',
      )
    })

    it('sends employee data including email', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockReceptionist)

      const receptionistData: EmployeeFormData = {
        ...mockEmployeeFormData,
        type: 'receptionist',
        email: 'receptionist@example.com',
      }

      await createReceptionistAPI(receptionistData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(receptionistData),
        }),
        expect.any(String),
      )
    })

    it('returns created Receptionist with ID', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockReceptionist)

      const result = await createReceptionistAPI({
        ...mockEmployeeFormData,
        type: 'receptionist',
      })

      expect(result).toEqual(mockReceptionist)
      expect(result).toHaveProperty('id')
      expect(result).toHaveProperty('email')
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Create failed'))

      await expect(
        createReceptionistAPI({ ...mockEmployeeFormData, type: 'receptionist' }),
      ).rejects.toThrow('Create failed')
    })
  })

  describe('updateReceptionistAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockReceptionist)

      await updateReceptionistAPI(123, { email: 'new@example.com' })

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/receptionists/123',
        expect.objectContaining({
          method: 'PUT',
        }),
        'Failed to update receptionist',
      )
    })

    it('returns updated Receptionist', async () => {
      const updatedReceptionist = { ...mockReceptionist, email: 'new@example.com' }
      vi.mocked(helpers.apiRequest).mockResolvedValue(updatedReceptionist)

      const result = await updateReceptionistAPI(1, { email: 'new@example.com' })

      expect(result).toEqual(updatedReceptionist)
      expect(result.email).toBe('new@example.com')
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Update failed'))

      await expect(updateReceptionistAPI(1, {})).rejects.toThrow('Update failed')
    })
  })

  describe('deleteReceptionistAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await deleteReceptionistAPI(123)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/receptionists/123',
        { method: 'DELETE' },
        'Failed to delete receptionist',
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await deleteReceptionistAPI(1)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Delete failed'))

      await expect(deleteReceptionistAPI(1)).rejects.toThrow('Delete failed')
    })
  })

  describe('Mutation hooks - Mechanics', () => {
    it('useCreateMechanicMutation can be called with dialogOpen ref', () => {
      const dialogOpen = ref(false)

      expect(() => {
        useCreateMechanicMutation(dialogOpen)
      }).not.toThrow()
    })

    it('useCreateMechanicMutation calls createMutationHook with correct parameters', () => {
      const dialogOpen = ref(false)

      useCreateMechanicMutation(dialogOpen)

      expect(helpers.createMutationHook).toHaveBeenCalledWith(
        'create-mechanic',
        createMechanicAPI,
        'mechanics',
        dialogOpen,
        'Error creating mechanic',
      )
    })

    it('useUpdateMechanicMutation calls createMutationHook', () => {
      const dialogOpen = ref(false)

      useUpdateMechanicMutation(dialogOpen)

      expect(helpers.createMutationHook).toHaveBeenCalledWith(
        'update-mechanic',
        expect.any(Function),
        'mechanics',
        dialogOpen,
        'Error updating mechanic',
      )
    })

    it('useDeleteMechanicMutation calls createMutationHook', () => {
      const dialogOpen = ref(false)

      useDeleteMechanicMutation(dialogOpen)

      expect(helpers.createMutationHook).toHaveBeenCalledWith(
        'delete-mechanic',
        deleteMechanicAPI,
        'mechanics',
        dialogOpen,
        'Error deleting mechanic',
      )
    })
  })

  describe('Mutation hooks - Receptionists', () => {
    it('useCreateReceptionistMutation can be called with dialogOpen ref', () => {
      const dialogOpen = ref(false)

      expect(() => {
        useCreateReceptionistMutation(dialogOpen)
      }).not.toThrow()
    })

    it('useCreateReceptionistMutation calls createMutationHook with correct parameters', () => {
      const dialogOpen = ref(false)

      useCreateReceptionistMutation(dialogOpen)

      expect(helpers.createMutationHook).toHaveBeenCalledWith(
        'create-receptionist',
        createReceptionistAPI,
        'receptionists',
        dialogOpen,
        'Error creating receptionist',
      )
    })

    it('useUpdateReceptionistMutation calls createMutationHook', () => {
      const dialogOpen = ref(false)

      useUpdateReceptionistMutation(dialogOpen)

      expect(helpers.createMutationHook).toHaveBeenCalledWith(
        'update-receptionist',
        expect.any(Function),
        'receptionists',
        dialogOpen,
        'Error updating receptionist',
      )
    })

    it('useDeleteReceptionistMutation calls createMutationHook', () => {
      const dialogOpen = ref(false)

      useDeleteReceptionistMutation(dialogOpen)

      expect(helpers.createMutationHook).toHaveBeenCalledWith(
        'delete-receptionist',
        deleteReceptionistAPI,
        'receptionists',
        dialogOpen,
        'Error deleting receptionist',
      )
    })
  })

  describe('Data types validation', () => {
    it('Mechanic has required fields', () => {
      expect(mockMechanic).toHaveProperty('id')
      expect(mockMechanic).toHaveProperty('firstName')
      expect(mockMechanic).toHaveProperty('lastName')
      expect(mockMechanic).toHaveProperty('birthDate')
      expect(mockMechanic).toHaveProperty('hireDate')
    })

    it('Receptionist has required fields including email', () => {
      expect(mockReceptionist).toHaveProperty('id')
      expect(mockReceptionist).toHaveProperty('firstName')
      expect(mockReceptionist).toHaveProperty('lastName')
      expect(mockReceptionist).toHaveProperty('birthDate')
      expect(mockReceptionist).toHaveProperty('hireDate')
      expect(mockReceptionist).toHaveProperty('email')
    })

    it('EmployeeFormData has type discriminator', () => {
      expect(mockEmployeeFormData).toHaveProperty('type')
      expect(['mechanic', 'receptionist']).toContain(mockEmployeeFormData.type)
    })
  })
})
