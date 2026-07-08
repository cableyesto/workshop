import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { logout } from '@/api/logout'
import * as authUtils from '@/utils/auth'

// Mock dependencies
vi.mock('@/utils/auth', () => ({
  clearToken: vi.fn(),
}))

describe('logout.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('logout', () => {
    it('calls clearToken from auth utils', () => {
      logout()

      expect(authUtils.clearToken).toHaveBeenCalledTimes(1)
    })

    it('calls clearToken without parameters', () => {
      logout()

      expect(authUtils.clearToken).toHaveBeenCalledWith()
    })

    it('returns void', () => {
      const result = logout()

      expect(result).toBeUndefined()
    })

    it('can be called multiple times', () => {
      logout()
      logout()
      logout()

      expect(authUtils.clearToken).toHaveBeenCalledTimes(3)
    })

    it('does not throw errors', () => {
      expect(() => {
        logout()
      }).not.toThrow()
    })

    it('delegates token clearing to clearToken', () => {
      // Arrange
      const clearTokenSpy = vi.mocked(authUtils.clearToken)

      // Act
      logout()

      // Assert
      expect(clearTokenSpy).toHaveBeenCalled()
    })
  })

  describe('Integration with clearToken', () => {
    it('clearToken is called when logout is executed', () => {
      const clearTokenMock = vi.mocked(authUtils.clearToken)
      clearTokenMock.mockClear()

      logout()

      expect(clearTokenMock).toHaveBeenCalled()
      expect(clearTokenMock.mock.calls).toHaveLength(1)
    })

    it('logout completes successfully when clearToken succeeds', () => {
      vi.mocked(authUtils.clearToken).mockImplementation(() => {
        // Simulate successful token clearing
      })

      expect(() => {
        logout()
      }).not.toThrow()
    })

    it('logout propagates errors from clearToken', () => {
      vi.mocked(authUtils.clearToken).mockImplementation(() => {
        throw new Error('Failed to clear token')
      })

      expect(() => {
        logout()
      }).toThrow('Failed to clear token')
    })
  })
})
