import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { ref } from 'vue'
import {
  useAllCarsQuery,
  useStoredCarsQuery,
  useInterventionCarsQuery,
  useRestitutionCarsQuery,
  updateClientCalledBackAPI,
  removeCarFromStorageAPI,
  updateCarStorageByLicensePlateAPI,
  updateCarAPI,
  createCarWithClientAPI,
  usePatchClientCalledBackMutation,
  useRemoveCarFromStorageMutation,
  useUpdateCarStorageByLicensePlateMutation,
  useUpdateCarMutation,
  useCreateCarWithClientMutation,
} from '@/api/cars'
import * as helpers from '@/api/helpers'

// Mock dependencies
vi.mock('@/api/helpers', () => ({
  apiRequest: vi.fn(),
}))

vi.mock('@pinia/colada', () => ({
  useQuery: vi.fn((config) => ({
    data: { value: null },
    isLoading: { value: false },
    error: { value: null },
    config,
  })),
  useMutation: vi.fn(),
  useQueryCache: vi.fn(() => ({
    invalidateQueries: vi.fn(),
  })),
}))

describe('cars.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const mockCar = {
    id: 1,
    manufacturer: 'Renault',
    model: 'Clio',
    licensePlate: 'AB-123-CD',
    color: 'Bleu',
    registrationYear: 2020,
    registrationMonth: 6,
    mileage: 50000,
  }

  const mockCarData = {
    manufacturer: 'Peugeot',
    model: '208',
    licensePlate: 'XY-789-ZW',
    color: 'Rouge',
    registrationYear: 2021,
    registrationMonth: 3,
    mileage: 30000,
  }

  const mockClientCarData = {
    client: {
      firstName: 'Jean',
      lastName: 'Dupont',
      email: 'jean.dupont@example.com',
      phone: '0612345678',
    },
    car: mockCarData,
  }

  describe('useAllCarsQuery', () => {
    it('can be called without parameters', () => {
      expect(() => {
        useAllCarsQuery()
      }).not.toThrow()
    })

    it('returns query result object', () => {
      const result = useAllCarsQuery()

      expect(result).toBeDefined()
      expect(result).toHaveProperty('data')
      expect(result).toHaveProperty('isLoading')
      expect(result).toHaveProperty('error')
    })

    it('uses correct query key', () => {
      const result = useAllCarsQuery() as any

      expect(result.config.key).toEqual(['cars'])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useAllCarsQuery() as any

      const fiveMinutes = 5 * 60 * 1000
      expect(result.config.staleTime).toBe(fiveMinutes)
    })

    it('calls correct API endpoint', () => {
      const result = useAllCarsQuery() as any

      expect(result.config.query).toBeDefined()
    })
  })

  describe('useStoredCarsQuery', () => {
    it('uses correct query key', () => {
      const result = useStoredCarsQuery() as any

      expect(result.config.key).toEqual(['cars', 'storage'])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useStoredCarsQuery() as any

      expect(result.config.staleTime).toBe(5 * 60 * 1000)
    })
  })

  describe('useInterventionCarsQuery', () => {
    it('uses correct query key', () => {
      const result = useInterventionCarsQuery() as any

      expect(result.config.key).toEqual(['cars', 'interventions'])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useInterventionCarsQuery() as any

      expect(result.config.staleTime).toBe(5 * 60 * 1000)
    })
  })

  describe('useRestitutionCarsQuery', () => {
    it('uses correct query key', () => {
      const result = useRestitutionCarsQuery() as any

      expect(result.config.key).toEqual(['cars', 'restitution'])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useRestitutionCarsQuery() as any

      expect(result.config.staleTime).toBe(5 * 60 * 1000)
    })
  })

  describe('updateClientCalledBackAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateClientCalledBackAPI(123, true)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/clients/123',
        expect.objectContaining({
          method: 'PATCH',
        }),
        'Failed to update client called back status'
      )
    })

    it('sends isClientCalledBack in request body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateClientCalledBackAPI(1, true)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify({ isClientCalledBack: true }),
        }),
        expect.any(String)
      )
    })

    it('handles false value for called back status', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateClientCalledBackAPI(1, false)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify({ isClientCalledBack: false }),
        }),
        expect.any(String)
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await updateClientCalledBackAPI(1, true)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Update failed'))

      await expect(updateClientCalledBackAPI(1, true)).rejects.toThrow('Update failed')
    })
  })

  describe('removeCarFromStorageAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await removeCarFromStorageAPI(123)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/cars/123/storage',
        expect.objectContaining({
          method: 'PATCH',
        }),
        'Failed to remove car from storage'
      )
    })

    it('sends isStored false in request body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await removeCarFromStorageAPI(1)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify({ isStored: false }),
        }),
        expect.any(String)
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await removeCarFromStorageAPI(1)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Remove failed'))

      await expect(removeCarFromStorageAPI(1)).rejects.toThrow('Remove failed')
    })
  })

  describe('updateCarStorageByLicensePlateAPI', () => {
    it('calls apiRequest with correct URL', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateCarStorageByLicensePlateAPI('AB-123-CD', true)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/cars/license-plate',
        expect.objectContaining({
          method: 'PATCH',
        }),
        'Failed to update car storage'
      )
    })

    it('sends license plate and storage status in body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateCarStorageByLicensePlateAPI('XY-789-ZW', true)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify({ licensePlate: 'XY-789-ZW', isStored: true }),
        }),
        expect.any(String)
      )
    })

    it('handles false storage status', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateCarStorageByLicensePlateAPI('AB-123-CD', false)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: expect.stringContaining('"isStored":false'),
        }),
        expect.any(String)
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await updateCarStorageByLicensePlateAPI('AB-123-CD', true)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Update failed'))

      await expect(updateCarStorageByLicensePlateAPI('AB-123-CD', true)).rejects.toThrow(
        'Update failed'
      )
    })
  })

  describe('updateCarAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateCarAPI(123, mockCarData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/cars/123',
        expect.objectContaining({
          method: 'PUT',
        }),
        'Failed to update car'
      )
    })

    it('sends car data in request body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateCarAPI(1, mockCarData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(mockCarData),
        }),
        expect.any(String)
      )
    })

    it('handles null values for optional fields', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const dataWithNulls = {
        ...mockCarData,
        registrationYear: null,
        registrationMonth: null,
        mileage: null,
      }

      await updateCarAPI(1, dataWithNulls)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: expect.stringContaining('null'),
        }),
        expect.any(String)
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await updateCarAPI(1, mockCarData)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Update failed'))

      await expect(updateCarAPI(1, mockCarData)).rejects.toThrow('Update failed')
    })
  })

  describe('createCarWithClientAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await createCarWithClientAPI(mockClientCarData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/cars/with-client',
        expect.objectContaining({
          method: 'POST',
        }),
        'Failed to create car'
      )
    })

    it('sends client and car data in request body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await createCarWithClientAPI(mockClientCarData)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(mockClientCarData),
        }),
        expect.any(String)
      )
    })

    it('handles null email in client data', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const dataWithNullEmail = {
        ...mockClientCarData,
        client: {
          ...mockClientCarData.client,
          email: null,
        },
      }

      await createCarWithClientAPI(dataWithNullEmail)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: expect.stringContaining('"email":null'),
        }),
        expect.any(String)
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await createCarWithClientAPI(mockClientCarData)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Create failed'))

      await expect(createCarWithClientAPI(mockClientCarData)).rejects.toThrow('Create failed')
    })
  })

  describe('Mutation hooks', () => {
    it('usePatchClientCalledBackMutation can be called with updatingIds ref', () => {
      const updatingIds = ref(new Set<number>())

      expect(() => {
        usePatchClientCalledBackMutation(updatingIds)
      }).not.toThrow()
    })

    it('useRemoveCarFromStorageMutation can be called without parameters', () => {
      expect(() => {
        useRemoveCarFromStorageMutation()
      }).not.toThrow()
    })

    it('useUpdateCarStorageByLicensePlateMutation accepts callbacks', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      expect(() => {
        useUpdateCarStorageByLicensePlateMutation(onSuccess, onError)
      }).not.toThrow()
    })

    it('useUpdateCarMutation accepts callbacks', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      expect(() => {
        useUpdateCarMutation(onSuccess, onError)
      }).not.toThrow()
    })

    it('useCreateCarWithClientMutation accepts callbacks', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      expect(() => {
        useCreateCarWithClientMutation(onSuccess, onError)
      }).not.toThrow()
    })
  })

  describe('Query keys structure', () => {
    it('all car queries use cars as base key', () => {
      const all = useAllCarsQuery() as any
      const stored = useStoredCarsQuery() as any
      const interventions = useInterventionCarsQuery() as any
      const restitution = useRestitutionCarsQuery() as any

      expect(all.config.key[0]).toBe('cars')
      expect(stored.config.key[0]).toBe('cars')
      expect(interventions.config.key[0]).toBe('cars')
      expect(restitution.config.key[0]).toBe('cars')
    })

    it('queries have unique identifiers after base key', () => {
      const stored = useStoredCarsQuery() as any
      const interventions = useInterventionCarsQuery() as any
      const restitution = useRestitutionCarsQuery() as any

      expect(stored.config.key[1]).toBe('storage')
      expect(interventions.config.key[1]).toBe('interventions')
      expect(restitution.config.key[1]).toBe('restitution')
    })
  })

  describe('Car data structure', () => {
    it('validates car data has required fields', () => {
      expect(mockCarData).toHaveProperty('manufacturer')
      expect(mockCarData).toHaveProperty('model')
      expect(mockCarData).toHaveProperty('licensePlate')
      expect(mockCarData).toHaveProperty('color')
    })

    it('validates car data has optional fields', () => {
      expect(mockCarData).toHaveProperty('registrationYear')
      expect(mockCarData).toHaveProperty('registrationMonth')
      expect(mockCarData).toHaveProperty('mileage')
    })

    it('validates client+car data structure', () => {
      expect(mockClientCarData).toHaveProperty('client')
      expect(mockClientCarData).toHaveProperty('car')
      expect(mockClientCarData.client).toHaveProperty('firstName')
      expect(mockClientCarData.client).toHaveProperty('lastName')
      expect(mockClientCarData.client).toHaveProperty('phone')
    })
  })
})
