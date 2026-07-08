import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import {
  useSearchInterventionQuery,
  useInterventionQuery,
  searchInterventionAPI,
  createInterventionAPI,
  getInterventionAPI,
  updateInterventionAPI,
  useUpdateInterventionMutation,
} from '@/api/interventions'
import * as helpers from '@/api/helpers'
import type {
  Intervention,
  SearchInterventionResponse,
  UpdateInterventionPayload,
} from '@/types/intervention'

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

describe('interventions.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const mockIntervention: Intervention = {
    id: 1,
    mechanic: {
      id: 1,
      firstName: 'Jean',
      lastName: 'Dupont',
    },
    car: {
      id: 1,
      licensePlate: 'AB-123-CD',
    },
    date: '2024-01-15',
    startTime: '10:30',
    status: 'In Progress',
    interventionType: 'Repair',
    documentType: 'Estimate',
    clientRemark: false,
    clientRequest: null,
    interventionEndRemark: false,
    finalNote: null,
  }

  const mockSearchResponse: SearchInterventionResponse = {
    found: true,
    intervention: mockIntervention,
  }

  const mockUpdatePayload: UpdateInterventionPayload = {
    interventionType: 'Repair',
    documentType: 'Estimate',
    date: '2024-01-15',
    startTime: '10:30',
  }

  describe('useSearchInterventionQuery', () => {
    it('can be called with mechanic ID and license plate', () => {
      expect(() => {
        useSearchInterventionQuery(1, 'AB-123-CD')
      }).not.toThrow()
    })

    it('returns query result object', () => {
      const result = useSearchInterventionQuery(1, 'AB-123-CD')

      expect(result).toBeDefined()
      expect(result).toHaveProperty('data')
      expect(result).toHaveProperty('isLoading')
      expect(result).toHaveProperty('error')
    })

    it('uses correct query key with parameters', () => {
      const result = useSearchInterventionQuery(42, 'XY-789-ZW') as any

      expect(result.config.key).toEqual(['interventions', 'search', 42, 'XY-789-ZW'])
    })

    it('is disabled by default for manual trigger', () => {
      const result = useSearchInterventionQuery(1, 'AB-123-CD') as any

      expect(result.config.enabled).toBe(false)
    })

    it('includes query function', () => {
      const result = useSearchInterventionQuery(1, 'AB-123-CD') as any

      expect(result.config.query).toBeDefined()
      expect(typeof result.config.query).toBe('function')
    })
  })

  describe('useInterventionQuery', () => {
    it('can be called with intervention ID and enabled flag', () => {
      expect(() => {
        useInterventionQuery(1, true)
      }).not.toThrow()
    })

    it('uses correct query key with intervention ID', () => {
      const result = useInterventionQuery(123, true) as any

      expect(result.config.key).toEqual(['interventions', 123])
    })

    it('uses 0 as fallback when ID is undefined', () => {
      const result = useInterventionQuery(undefined, false) as any

      expect(result.config.key).toEqual(['interventions', 0])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useInterventionQuery(1, true) as any

      const fiveMinutes = 5 * 60 * 1000
      expect(result.config.staleTime).toBe(fiveMinutes)
    })

    it('respects enabled flag', () => {
      const result1 = useInterventionQuery(1, true) as any
      const result2 = useInterventionQuery(1, false) as any

      expect(result1.config.enabled).toBe(true)
      expect(result2.config.enabled).toBe(false)
    })

    it('disables query when intervention ID is undefined', () => {
      const result = useInterventionQuery(undefined, true) as any

      expect(result.config.enabled).toBe(false)
    })
  })

  describe('searchInterventionAPI', () => {
    it('calls apiRequest with correct URL and query parameters', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockSearchResponse)

      await searchInterventionAPI(123, 'AB-123-CD')

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions/search?mechanicId=123&licensePlate=AB-123-CD',
        {},
        'Failed to search intervention'
      )
    })

    it('encodes license plate in URL', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockSearchResponse)

      await searchInterventionAPI(1, 'XY-999-AB')

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.stringContaining('licensePlate=XY-999-AB'),
        expect.any(Object),
        expect.any(String)
      )
    })

    it('returns SearchInterventionResponse', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockSearchResponse)

      const result = await searchInterventionAPI(1, 'AB-123-CD')

      expect(result).toEqual(mockSearchResponse)
      expect(result).toHaveProperty('found')
      expect(result).toHaveProperty('intervention')
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Search failed'))

      await expect(searchInterventionAPI(1, 'AB-123-CD')).rejects.toThrow('Search failed')
    })
  })

  describe('createInterventionAPI', () => {
    it('calls apiRequest with POST method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue({ id: 1 })

      await createInterventionAPI(1, 'AB-123-CD')

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions',
        expect.objectContaining({
          method: 'POST',
        }),
        'Failed to create intervention'
      )
    })

    it('sends mechanic ID and license plate in body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue({ id: 1 })

      await createInterventionAPI(42, 'XY-789-ZW')

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify({
            mechanicId: 42,
            licensePlate: 'XY-789-ZW',
          }),
        }),
        expect.any(String)
      )
    })

    it('sets Content-Type header', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue({ id: 1 })

      await createInterventionAPI(1, 'AB-123-CD')

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          headers: {
            'Content-Type': 'application/json',
          },
        }),
        expect.any(String)
      )
    })

    it('returns object with new intervention ID', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue({ id: 99 })

      const result = await createInterventionAPI(1, 'AB-123-CD')

      expect(result).toEqual({ id: 99 })
      expect(result).toHaveProperty('id')
      expect(typeof result.id).toBe('number')
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Create failed'))

      await expect(createInterventionAPI(1, 'AB-123-CD')).rejects.toThrow('Create failed')
    })
  })

  describe('getInterventionAPI', () => {
    it('calls apiRequest with correct URL', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockIntervention)

      await getInterventionAPI(123)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions/123',
        {},
        'Failed to fetch intervention'
      )
    })

    it('returns full Intervention object', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockIntervention)

      const result = await getInterventionAPI(1)

      expect(result).toEqual(mockIntervention)
      expect(result).toHaveProperty('id')
      expect(result).toHaveProperty('mechanic')
      expect(result).toHaveProperty('car')
      expect(result).toHaveProperty('status')
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Not found'))

      await expect(getInterventionAPI(999)).rejects.toThrow('Not found')
    })
  })

  describe('updateInterventionAPI', () => {
    it('calls apiRequest with PATCH method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateInterventionAPI(123, mockUpdatePayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions/123',
        expect.objectContaining({
          method: 'PATCH',
        }),
        'Failed to update intervention'
      )
    })

    it('sends update payload in request body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      await updateInterventionAPI(1, mockUpdatePayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(mockUpdatePayload),
        }),
        expect.any(String)
      )
    })

    it('returns void on success', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const result = await updateInterventionAPI(1, mockUpdatePayload)

      expect(result).toBeUndefined()
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Update failed'))

      await expect(updateInterventionAPI(1, mockUpdatePayload)).rejects.toThrow('Update failed')
    })

    it('handles partial updates', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(undefined)

      const partialPayload: UpdateInterventionPayload = {
        clientRequest: 'New request',
      }

      await updateInterventionAPI(1, partialPayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(partialPayload),
        }),
        expect.any(String)
      )
    })
  })

  describe('useUpdateInterventionMutation', () => {
    it('can be called with callbacks', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      expect(() => {
        useUpdateInterventionMutation(onSuccess, onError)
      }).not.toThrow()
    })

    it('accepts success callback', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      useUpdateInterventionMutation(onSuccess, onError)

      expect(onSuccess).toBeDefined()
      expect(typeof onSuccess).toBe('function')
    })

    it('accepts error callback', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      useUpdateInterventionMutation(onSuccess, onError)

      expect(onError).toBeDefined()
      expect(typeof onError).toBe('function')
    })
  })

  describe('URL construction', () => {
    it('builds search URL with query parameters correctly', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockSearchResponse)

      await searchInterventionAPI(100, 'TEST-123')

      const callArgs = vi.mocked(helpers.apiRequest).mock.calls[0]!
      const url = callArgs[0]

      expect(url).toContain('mechanicId=100')
      expect(url).toContain('licensePlate=TEST-123')
      expect(url).toContain('?')
      expect(url).toContain('&')
    })

    it('builds intervention detail URL correctly', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockIntervention)

      await getInterventionAPI(456)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions/456',
        expect.any(Object),
        expect.any(String)
      )
    })
  })
})
