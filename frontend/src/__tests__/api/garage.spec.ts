import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { useGarageQuery } from '@/api/garage'
import * as authUtils from '@/utils/auth'

// Mock dependencies
vi.mock('@/utils/auth', () => ({
  getToken: vi.fn(),
}))

vi.mock('@pinia/colada', () => ({
  useQuery: vi.fn((config) => {
    // Return a mock query result with the config
    return {
      data: { value: null },
      isLoading: { value: false },
      error: { value: null },
      config,
    }
  }),
}))

describe('garage.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(authUtils.getToken).mockReturnValue('test-token')
  })

  describe('useGarageQuery', () => {
    it('can be called with garage ID', () => {
      expect(() => {
        useGarageQuery(1)
      }).not.toThrow()
    })

    it('accepts numeric ID parameter', () => {
      expect(() => {
        useGarageQuery(123)
      }).not.toThrow()

      expect(() => {
        useGarageQuery(456)
      }).not.toThrow()
    })

    it('returns query result object', () => {
      const result = useGarageQuery(1)

      expect(result).toBeDefined()
      expect(result).toHaveProperty('data')
      expect(result).toHaveProperty('isLoading')
      expect(result).toHaveProperty('error')
    })

    it('uses correct query key with garage ID', () => {
      const result = useGarageQuery(42) as any

      // Check that config contains the key
      expect(result.config).toBeDefined()
      expect(result.config.key).toEqual(['garage', 42])
    })

    it('configures staleTime to Infinity', () => {
      const result = useGarageQuery(1) as any

      expect(result.config.staleTime).toBe(Infinity)
    })

    it('disables refetch on window focus', () => {
      const result = useGarageQuery(1) as any

      expect(result.config.refetchOnWindowFocus).toBe(false)
    })

    it('includes query function', () => {
      const result = useGarageQuery(1) as any

      expect(result.config.query).toBeDefined()
      expect(typeof result.config.query).toBe('function')
    })
  })

  describe('Query function behavior', () => {
    it('query function uses fetch with correct URL', async () => {
      const mockFetch = vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({
          id: 1,
          name: 'Test Garage',
          siret: '12345678901234',
          timeSlots: [],
        }),
      })
      global.fetch = mockFetch

      const result = useGarageQuery(123) as any
      await result.config.query()

      expect(mockFetch).toHaveBeenCalledWith(
        '/api/garages/123',
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer test-token',
            'Content-Type': 'application/json',
          }),
        }),
      )
    })

    it('query function includes auth token', async () => {
      vi.mocked(authUtils.getToken).mockReturnValue('my-secret-token')

      const mockFetch = vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({ id: 1, name: 'Garage', siret: '123', timeSlots: [] }),
      })
      global.fetch = mockFetch

      const result = useGarageQuery(1) as any
      await result.config.query()

      expect(mockFetch).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer my-secret-token',
          }),
        }),
      )
    })

    it('query function throws error on failed response', async () => {
      const mockFetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 404,
        json: async () => ({ error: 'Garage not found' }),
      })
      global.fetch = mockFetch

      const result = useGarageQuery(999) as any

      await expect(result.config.query()).rejects.toThrow('Garage not found')
    })

    it('query function uses default error message on malformed response', async () => {
      const mockFetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 500,
        json: async () => {
          throw new Error('Invalid JSON')
        },
      })
      global.fetch = mockFetch

      const result = useGarageQuery(1) as any

      await expect(result.config.query()).rejects.toThrow('Failed to fetch garage')
    })

    it('query function returns garage data on success', async () => {
      const mockGarageData = {
        id: 5,
        name: 'Super Garage',
        siret: '98765432109876',
        timeSlots: [
          { id: 1, startTime: '08:00', endTime: '12:00' },
          { id: 2, startTime: '14:00', endTime: '18:00' },
        ],
      }

      const mockFetch = vi.fn().mockResolvedValue({
        ok: true,
        json: async () => mockGarageData,
      })
      global.fetch = mockFetch

      const result = useGarageQuery(5) as any
      const data = await result.config.query()

      expect(data).toEqual(mockGarageData)
      expect(data.id).toBe(5)
      expect(data.name).toBe('Super Garage')
      expect(data.timeSlots).toHaveLength(2)
    })
  })

  describe('Query configuration', () => {
    it('uses staleTime Infinity to cache indefinitely', () => {
      const result = useGarageQuery(1) as any

      // Infinity means data never becomes stale
      expect(result.config.staleTime).toBe(Infinity)
      expect(Number.isFinite(result.config.staleTime)).toBe(false)
    })

    it('disables automatic refetch on window focus', () => {
      const result = useGarageQuery(1) as any

      // Prevents unnecessary refetches when user switches tabs
      expect(result.config.refetchOnWindowFocus).toBe(false)
      expect(result.config.refetchOnWindowFocus).not.toBe(true)
    })

    it('generates unique key for each garage ID', () => {
      const result1 = useGarageQuery(1) as any
      const result2 = useGarageQuery(2) as any
      const result3 = useGarageQuery(100) as any

      expect(result1.config.key).toEqual(['garage', 1])
      expect(result2.config.key).toEqual(['garage', 2])
      expect(result3.config.key).toEqual(['garage', 100])

      // Keys should be different
      expect(result1.config.key).not.toEqual(result2.config.key)
    })
  })
})
