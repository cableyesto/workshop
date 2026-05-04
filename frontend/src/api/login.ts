import type { LoginOwnerRequest, LoginReceptionistRequest, LoginResponse } from '@/types/auth'

/**
 * Login for Owner users (no SIRET required)
 */
export async function loginOwner(credentials: LoginOwnerRequest): Promise<LoginResponse> {
  const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(credentials),
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Login failed' }))
    throw new Error(error.error || 'Invalid credentials')
  }

  return response.json()
}

/**
 * Login for Receptionist users (SIRET required)
 */
export async function loginReceptionist(
  credentials: LoginReceptionistRequest,
): Promise<LoginResponse> {
  const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(credentials),
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Login failed' }))
    throw new Error(error.error || 'Invalid credentials')
  }

  return response.json()
}
