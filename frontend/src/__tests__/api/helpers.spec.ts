import { describe, it, expect, vi, beforeEach, afterEach } from 'vite-plus/test'
import { apiRequest, createMutationHook } from '@/api/helpers'
import * as authUtils from '@/utils/auth'
import { ref } from 'vue'

// Mock dependencies
vi.mock('@/utils/auth', () => ({
  getToken: vi.fn(),
}))

vi.mock('@pinia/colada', () => ({
  useMutation: vi.fn(),
  useQueryCache: vi.fn(),
}))

describe('apiRequest', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    // Mock getToken to return a test token
    vi.mocked(authUtils.getToken).mockReturnValue('test-token-123')
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('Successful Requests', () => {
    it('makes a successful GET request with auth token', async () => {
      const mockData = { id: 1, name: 'Test' }
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => mockData,
      })

      const result = await apiRequest<typeof mockData>('https://api.test.com/data')

      expect(global.fetch).toHaveBeenCalledWith(
        'https://api.test.com/data',
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer test-token-123',
            'Content-Type': 'application/json',
          }),
        }),
      )
      expect(result).toEqual(mockData)
    })

    it('includes custom headers in request', async () => {
      const mockData = { success: true }
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => mockData,
      })

      await apiRequest('https://api.test.com/data', {
        headers: {
          'X-Custom-Header': 'custom-value',
        },
      })

      expect(global.fetch).toHaveBeenCalledWith(
        'https://api.test.com/data',
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer test-token-123',
            'Content-Type': 'application/json',
            'X-Custom-Header': 'custom-value',
          }),
        }),
      )
    })

    it('handles POST request with body', async () => {
      const mockData = { id: 1, created: true }
      const postData = { name: 'New Item' }

      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        status: 201,
        json: async () => mockData,
      })

      const result = await apiRequest<typeof mockData>('https://api.test.com/items', {
        method: 'POST',
        body: JSON.stringify(postData),
      })

      expect(global.fetch).toHaveBeenCalledWith(
        'https://api.test.com/items',
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify(postData),
        }),
      )
      expect(result).toEqual(mockData)
    })

    it('handles 204 No Content response', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        status: 204,
      })

      const result = await apiRequest('https://api.test.com/items/1', {
        method: 'DELETE',
      })

      expect(result).toBeUndefined()
    })
  })

  describe('Error Handling', () => {
    it('throws error when response is not ok', async () => {
      const errorMessage = 'Not found'
      global.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 404,
        json: async () => ({ error: errorMessage }),
      })

      await expect(apiRequest('https://api.test.com/nonexistent')).rejects.toThrow(errorMessage)
    })

    it('uses default error message when response has no error field', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 500,
        json: async () => ({}),
      })

      await expect(
        apiRequest('https://api.test.com/error', {}, 'Custom error message'),
      ).rejects.toThrow('Custom error message')
    })

    it('handles malformed JSON error response', async () => {
      const customError = 'Failed to process'
      global.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 500,
        json: async () => {
          throw new Error('Invalid JSON')
        },
      })

      await expect(apiRequest('https://api.test.com/error', {}, customError)).rejects.toThrow(
        customError,
      )
    })

    it('throws error with response error message', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 400,
        json: async () => ({ error: 'Validation failed' }),
      })

      await expect(apiRequest('https://api.test.com/validate')).rejects.toThrow('Validation failed')
    })
  })

  describe('Authentication', () => {
    it('includes Bearer token from getToken', async () => {
      vi.mocked(authUtils.getToken).mockReturnValue('my-secret-token')

      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => ({ data: 'test' }),
      })

      await apiRequest('https://api.test.com/protected')

      expect(global.fetch).toHaveBeenCalledWith(
        'https://api.test.com/protected',
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer my-secret-token',
          }),
        }),
      )
    })

    it('works when token is null', async () => {
      vi.mocked(authUtils.getToken).mockReturnValue(null)

      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => ({ data: 'test' }),
      })

      await apiRequest('https://api.test.com/data')

      expect(global.fetch).toHaveBeenCalledWith(
        'https://api.test.com/data',
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer null',
          }),
        }),
      )
    })
  })
})

describe('createMutationHook', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  // Note: createMutationHook is a thin wrapper around Pinia Colada's useMutation
  // Testing it thoroughly would require testing Pinia Colada internals
  // For unit tests, we verify it can be called without errors

  it('can be called with correct parameters', () => {
    const dialogOpen = ref(false)
    const mutationFn = vi.fn()

    // Just verify the function can be called without throwing
    expect(() => {
      createMutationHook('test-mutation', mutationFn, 'test-cache', dialogOpen, 'Test error')
    }).not.toThrow()
  })

  it('accepts all required parameters', () => {
    const dialogOpen = ref(true)
    const mutationFn = vi.fn(async () => ({ success: true }))

    // Just verify it doesn't throw with different parameter types
    expect(() => {
      createMutationHook(
        'create-item',
        mutationFn,
        'items-cache',
        dialogOpen,
        'Error creating item',
      )
    }).not.toThrow()
  })
})
