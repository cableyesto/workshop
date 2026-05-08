import { describe, it, expect, beforeEach, vi } from 'vite-plus/test'
import { getTokenPayload, isAuthenticated, hasRole } from '../../utils/auth'
import type { JWTPayload } from '../../types/auth'

// Mock jwt-decode
vi.mock('jwt-decode', () => ({
  jwtDecode: vi.fn(),
}))

describe('auth utils', () => {
  beforeEach(() => {
    // Clear localStorage before each test
    localStorage.clear()
    vi.clearAllMocks()
  })

  describe('getTokenPayload', () => {
    it('returns null when no token in localStorage', () => {
      expect(getTokenPayload()).toBeNull()
    })

    it('returns null when token is invalid', async () => {
      const { jwtDecode } = await import('jwt-decode')
      vi.mocked(jwtDecode).mockImplementation(() => {
        throw new Error('Invalid token')
      })

      localStorage.setItem('jwt_token', 'invalid.token.here')

      expect(getTokenPayload()).toBeNull()
    })

    it('returns decoded payload when token is valid', async () => {
      const { jwtDecode } = await import('jwt-decode')
      const mockPayload: JWTPayload = {
        exp: Math.floor(Date.now() / 1000) + 3600,
        iat: Math.floor(Date.now() / 1000),
        roles: ['ROLE_RECEPTIONIST'],
        username: 'test@example.com',
      }

      vi.mocked(jwtDecode).mockReturnValue(mockPayload)
      localStorage.setItem('jwt_token', 'valid.jwt.token')

      const result = getTokenPayload()

      expect(result).toEqual(mockPayload)
      expect(jwtDecode).toHaveBeenCalledWith('valid.jwt.token')
    })
  })

  describe('isAuthenticated', () => {
    it('returns false when no token exists', () => {
      expect(isAuthenticated()).toBe(false)
    })

    it('returns false when token is expired', async () => {
      const { jwtDecode } = await import('jwt-decode')
      const expiredPayload: JWTPayload = {
        exp: Math.floor(Date.now() / 1000) - 3600, // Expired 1 hour ago
        iat: Math.floor(Date.now() / 1000) - 7200,
        roles: ['ROLE_OWNER'],
        username: 'owner@example.com',
      }

      vi.mocked(jwtDecode).mockReturnValue(expiredPayload)
      localStorage.setItem('jwt_token', 'expired.token')

      expect(isAuthenticated()).toBe(false)
    })

    it('returns true when token is valid and not expired', async () => {
      const { jwtDecode } = await import('jwt-decode')
      const validPayload: JWTPayload = {
        exp: Math.floor(Date.now() / 1000) + 3600, // Expires in 1 hour
        iat: Math.floor(Date.now() / 1000),
        roles: ['ROLE_RECEPTIONIST'],
        username: 'receptionist@example.com',
      }

      vi.mocked(jwtDecode).mockReturnValue(validPayload)
      localStorage.setItem('jwt_token', 'valid.token')

      expect(isAuthenticated()).toBe(true)
    })
  })

  describe('hasRole', () => {
    it('returns false when no token exists', () => {
      expect(hasRole('ROLE_OWNER')).toBe(false)
    })

    it('returns false when token has no roles array', async () => {
      const { jwtDecode } = await import('jwt-decode')
      const payloadWithoutRoles = {
        exp: Math.floor(Date.now() / 1000) + 3600,
        iat: Math.floor(Date.now() / 1000),
        username: 'test@example.com',
      } as JWTPayload

      vi.mocked(jwtDecode).mockReturnValue(payloadWithoutRoles)
      localStorage.setItem('jwt_token', 'token')

      expect(hasRole('ROLE_OWNER')).toBe(false)
    })

    it('returns false when role is not in roles array', async () => {
      const { jwtDecode } = await import('jwt-decode')
      const payload: JWTPayload = {
        exp: Math.floor(Date.now() / 1000) + 3600,
        iat: Math.floor(Date.now() / 1000),
        roles: ['ROLE_RECEPTIONIST'],
        username: 'test@example.com',
      }

      vi.mocked(jwtDecode).mockReturnValue(payload)
      localStorage.setItem('jwt_token', 'token')

      expect(hasRole('ROLE_OWNER')).toBe(false)
    })

    it('returns true when role is in roles array', async () => {
      const { jwtDecode } = await import('jwt-decode')
      const payload: JWTPayload = {
        exp: Math.floor(Date.now() / 1000) + 3600,
        iat: Math.floor(Date.now() / 1000),
        roles: ['ROLE_OWNER', 'ROLE_USER'],
        username: 'owner@example.com',
      }

      vi.mocked(jwtDecode).mockReturnValue(payload)
      localStorage.setItem('jwt_token', 'token')

      expect(hasRole('ROLE_OWNER')).toBe(true)
      expect(hasRole('ROLE_USER')).toBe(true)
    })
  })
})
