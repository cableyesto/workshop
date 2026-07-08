import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { useManufacturersQuery } from '@/api/manufacturers'
import * as helpers from '@/api/helpers'

// Mock dependencies
vi.mock('@/api/helpers', () => ({
  apiRequest: vi.fn(),
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

describe('manufacturers.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('useManufacturersQuery', () => {
    it('can be called without parameters', () => {
      expect(() => {
        useManufacturersQuery()
      }).not.toThrow()
    })

    it('returns query result object', () => {
      const result = useManufacturersQuery()

      expect(result).toBeDefined()
      expect(result).toHaveProperty('data')
      expect(result).toHaveProperty('isLoading')
      expect(result).toHaveProperty('error')
    })

    it('uses correct query key', () => {
      const result = useManufacturersQuery() as any

      expect(result.config).toBeDefined()
      expect(result.config.key).toEqual(['manufacturers'])
    })

    it('configures staleTime to 1 hour', () => {
      const result = useManufacturersQuery() as any

      const oneHour = 60 * 60 * 1000
      expect(result.config.staleTime).toBe(oneHour)
      expect(result.config.staleTime).toBe(3600000)
    })

    it('includes query function', () => {
      const result = useManufacturersQuery() as any

      expect(result.config.query).toBeDefined()
      expect(typeof result.config.query).toBe('function')
    })
  })

  describe('Query function behavior', () => {
    it('query function calls apiRequest with correct URL', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue([
        { id: 1, name: 'Toyota' },
        { id: 2, name: 'Honda' },
      ])

      const result = useManufacturersQuery() as any
      await result.config.query()

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/manufacturers',
        {},
        'Failed to fetch manufacturers',
      )
    })

    it('query function passes empty options object', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue([])

      const result = useManufacturersQuery() as any
      await result.config.query()

      expect(helpers.apiRequest).toHaveBeenCalledWith(expect.any(String), {}, expect.any(String))
    })

    it('query function passes custom error message', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue([])

      const result = useManufacturersQuery() as any
      await result.config.query()

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.any(Object),
        'Failed to fetch manufacturers',
      )
    })

    it('query function returns array of manufacturers', async () => {
      const mockManufacturers = [
        { id: 1, name: 'Renault' },
        { id: 2, name: 'Peugeot' },
        { id: 3, name: 'Citroën' },
      ]
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockManufacturers)

      const result = useManufacturersQuery() as any
      const data = await result.config.query()

      expect(data).toEqual(mockManufacturers)
      expect(data).toHaveLength(3)
      expect(data[0]).toHaveProperty('id')
      expect(data[0]).toHaveProperty('name')
    })

    it('query function handles empty manufacturers array', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue([])

      const result = useManufacturersQuery() as any
      const data = await result.config.query()

      expect(data).toEqual([])
      expect(data).toHaveLength(0)
    })

    it('query function propagates apiRequest errors', async () => {
      const error = new Error('Network error')
      vi.mocked(helpers.apiRequest).mockRejectedValue(error)

      const result = useManufacturersQuery() as any

      await expect(result.config.query()).rejects.toThrow('Network error')
    })
  })

  describe('Query configuration', () => {
    it('uses staleTime of 1 hour for caching', () => {
      const result = useManufacturersQuery() as any

      // 1 hour = 60 minutes * 60 seconds * 1000 milliseconds
      const expectedStaleTime = 60 * 60 * 1000
      expect(result.config.staleTime).toBe(expectedStaleTime)
    })

    it('cache duration is reasonable for rarely changing data', () => {
      const result = useManufacturersQuery() as any

      // Manufacturers rarely change, so 1 hour cache is appropriate
      const oneHourInMs = 3600000
      expect(result.config.staleTime).toBe(oneHourInMs)

      // Should be less than a day but more than 5 minutes
      expect(result.config.staleTime).toBeGreaterThan(5 * 60 * 1000)
      expect(result.config.staleTime).toBeLessThan(24 * 60 * 60 * 1000)
    })

    it('uses simple array key for global manufacturers cache', () => {
      const result = useManufacturersQuery() as any

      // No ID parameter needed - all manufacturers fetched together
      expect(result.config.key).toEqual(['manufacturers'])
      expect(result.config.key).toHaveLength(1)
      expect(Array.isArray(result.config.key)).toBe(true)
    })
  })

  describe('Manufacturer data structure', () => {
    it('validates manufacturer has required fields', async () => {
      const mockManufacturer = { id: 1, name: 'Ford' }
      vi.mocked(helpers.apiRequest).mockResolvedValue([mockManufacturer])

      const result = useManufacturersQuery() as any
      const data = await result.config.query()

      expect(data[0]).toHaveProperty('id')
      expect(data[0]).toHaveProperty('name')
      expect(typeof data[0].id).toBe('number')
      expect(typeof data[0].name).toBe('string')
    })

    it('handles multiple manufacturers', async () => {
      const mockManufacturers = [
        { id: 1, name: 'Volkswagen' },
        { id: 2, name: 'BMW' },
        { id: 3, name: 'Mercedes' },
        { id: 4, name: 'Audi' },
      ]
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockManufacturers)

      const result = useManufacturersQuery() as any
      const data = await result.config.query()

      expect(data).toHaveLength(4)
      expect(data.every((m: any) => typeof m.id === 'number')).toBe(true)
      expect(data.every((m: any) => typeof m.name === 'string')).toBe(true)
    })
  })
})
