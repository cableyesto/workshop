import { describe, it, expect, vi, beforeEach, afterEach } from 'vite-plus/test'
import { loginOwner, loginReceptionist } from '@/api/login'
import type { LoginOwnerRequest, LoginReceptionistRequest, LoginResponse } from '@/types/auth'

describe('login.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('loginOwner', () => {
    const validOwnerCredentials: LoginOwnerRequest = {
      email: 'owner@example.com',
      password: 'password123',
    }

    const mockSuccessResponse: LoginResponse = {
      token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
    }

    describe('Successful login', () => {
      it('calls fetch with correct URL and method', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginOwner(validOwnerCredentials)

        expect(global.fetch).toHaveBeenCalledWith(
          '/api/login',
          expect.objectContaining({
            method: 'POST',
          })
        )
      })

      it('sends credentials as JSON in request body', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginOwner(validOwnerCredentials)

        expect(global.fetch).toHaveBeenCalledWith(
          expect.any(String),
          expect.objectContaining({
            body: JSON.stringify(validOwnerCredentials),
          })
        )
      })

      it('sets Content-Type header to application/json', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginOwner(validOwnerCredentials)

        expect(global.fetch).toHaveBeenCalledWith(
          expect.any(String),
          expect.objectContaining({
            headers: {
              'Content-Type': 'application/json',
            },
          })
        )
      })

      it('returns LoginResponse with token', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        const response = await loginOwner(validOwnerCredentials)

        expect(response).toEqual(mockSuccessResponse)
        expect(response).toHaveProperty('token')
        expect(typeof response.token).toBe('string')
      })

      it('handles successful login with different credentials', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        const credentials: LoginOwnerRequest = {
          email: 'different@example.com',
          password: 'different-password',
        }

        const response = await loginOwner(credentials)

        expect(response).toBeDefined()
        expect(response.token).toBeDefined()
      })
    })

    describe('Failed login', () => {
      it('throws error when response is not ok', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: false,
          status: 401,
          json: async () => ({ error: 'Invalid credentials' }),
        })

        await expect(loginOwner(validOwnerCredentials)).rejects.toThrow('Invalid credentials')
      })

      it('uses default error message when response has no error field', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: false,
          status: 401,
          json: async () => ({}),
        })

        await expect(loginOwner(validOwnerCredentials)).rejects.toThrow('Invalid credentials')
      })

      it('handles malformed JSON error response', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: false,
          status: 500,
          json: async () => {
            throw new Error('Invalid JSON')
          },
        })

        await expect(loginOwner(validOwnerCredentials)).rejects.toThrow('Login failed')
      })

      it('throws error on 500 server error', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: false,
          status: 500,
          json: async () => ({ error: 'Internal server error' }),
        })

        await expect(loginOwner(validOwnerCredentials)).rejects.toThrow('Internal server error')
      })

      it('throws error on 403 forbidden', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: false,
          status: 403,
          json: async () => ({ error: 'Access denied' }),
        })

        await expect(loginOwner(validOwnerCredentials)).rejects.toThrow('Access denied')
      })
    })

    describe('Request validation', () => {
      it('sends email and password fields', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginOwner(validOwnerCredentials)

        const callArgs = vi.mocked(global.fetch).mock.calls[0]!
        const body = JSON.parse(callArgs[1]?.body as string)

        expect(body).toHaveProperty('email')
        expect(body).toHaveProperty('password')
        expect(body.email).toBe('owner@example.com')
        expect(body.password).toBe('password123')
      })

      it('does not send SIRET field for owner login', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginOwner(validOwnerCredentials)

        const callArgs = vi.mocked(global.fetch).mock.calls[0]!
        const body = JSON.parse(callArgs[1]?.body as string)

        expect(body).not.toHaveProperty('siret')
      })
    })
  })

  describe('loginReceptionist', () => {
    const validReceptionistCredentials: LoginReceptionistRequest = {
      siret: '12345678901234',
      email: 'receptionist@example.com',
      password: 'password456',
    }

    const mockSuccessResponse: LoginResponse = {
      token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
    }

    describe('Successful login', () => {
      it('calls fetch with correct URL and method', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginReceptionist(validReceptionistCredentials)

        expect(global.fetch).toHaveBeenCalledWith(
          '/api/login',
          expect.objectContaining({
            method: 'POST',
          })
        )
      })

      it('sends credentials including SIRET as JSON', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginReceptionist(validReceptionistCredentials)

        expect(global.fetch).toHaveBeenCalledWith(
          expect.any(String),
          expect.objectContaining({
            body: JSON.stringify(validReceptionistCredentials),
          })
        )
      })

      it('returns LoginResponse with token', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        const response = await loginReceptionist(validReceptionistCredentials)

        expect(response).toEqual(mockSuccessResponse)
        expect(response).toHaveProperty('token')
        expect(typeof response.token).toBe('string')
      })
    })

    describe('Failed login', () => {
      it('throws error when response is not ok', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: false,
          status: 401,
          json: async () => ({ error: 'Invalid credentials' }),
        })

        await expect(loginReceptionist(validReceptionistCredentials)).rejects.toThrow(
          'Invalid credentials'
        )
      })

      it('handles malformed JSON error response', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: false,
          status: 500,
          json: async () => {
            throw new Error('Invalid JSON')
          },
        })

        await expect(loginReceptionist(validReceptionistCredentials)).rejects.toThrow(
          'Login failed'
        )
      })
    })

    describe('Request validation', () => {
      it('sends siret, email and password fields', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginReceptionist(validReceptionistCredentials)

        const callArgs = vi.mocked(global.fetch).mock.calls[0]!
        const body = JSON.parse(callArgs[1]?.body as string)

        expect(body).toHaveProperty('siret')
        expect(body).toHaveProperty('email')
        expect(body).toHaveProperty('password')
        expect(body.siret).toBe('12345678901234')
        expect(body.email).toBe('receptionist@example.com')
        expect(body.password).toBe('password456')
      })

      it('SIRET field is required for receptionist login', async () => {
        global.fetch = vi.fn().mockResolvedValue({
          ok: true,
          json: async () => mockSuccessResponse,
        })

        await loginReceptionist(validReceptionistCredentials)

        const callArgs = vi.mocked(global.fetch).mock.calls[0]!
        const body = JSON.parse(callArgs[1]?.body as string)

        expect(body.siret).toBeDefined()
        expect(body.siret).toBe('12345678901234')
      })
    })
  })

  describe('Common behavior', () => {
    it('both functions use the same endpoint', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({ token: 'test-token' }),
      })

      await loginOwner({ email: 'owner@test.com', password: 'pass' })
      await loginReceptionist({
        siret: '12345678901234',
        email: 'receptionist@test.com',
        password: 'pass',
      })

      expect(global.fetch).toHaveBeenNthCalledWith(
        1,
        '/api/login',
        expect.any(Object)
      )
      expect(global.fetch).toHaveBeenNthCalledWith(
        2,
        '/api/login',
        expect.any(Object)
      )
    })

    it('both functions use POST method', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({ token: 'test-token' }),
      })

      await loginOwner({ email: 'test@test.com', password: 'pass' })
      await loginReceptionist({
        siret: '12345678901234',
        email: 'test@test.com',
        password: 'pass',
      })

      const calls = vi.mocked(global.fetch).mock.calls
      expect(calls[0]![1]?.method).toBe('POST')
      expect(calls[1]![1]?.method).toBe('POST')
    })

    it('both functions return the same response structure', async () => {
      const mockToken = 'test-jwt-token-12345'
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({ token: mockToken }),
      })

      const ownerResponse = await loginOwner({ email: 'owner@test.com', password: 'pass' })
      const receptionistResponse = await loginReceptionist({
        siret: '12345678901234',
        email: 'receptionist@test.com',
        password: 'pass',
      })

      expect(ownerResponse).toEqual({ token: mockToken })
      expect(receptionistResponse).toEqual({ token: mockToken })
    })
  })
})
