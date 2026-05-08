import { jwtDecode } from 'jwt-decode'
import type { JWTPayload } from '@/types/auth'

/**
 * Get JWT token from localStorage
 */
export function getToken(): string | null {
  return localStorage.getItem('jwt_token')
}

/**
 * Set JWT token in localStorage
 */
export function setToken(token: string): void {
  localStorage.setItem('jwt_token', token)
}

/**
 * Clear JWT token from localStorage
 */
export function clearToken(): void {
  localStorage.removeItem('jwt_token')
}

/**
 * Decode JWT token and return payload
 * Returns null if token is missing or invalid
 */
export function getTokenPayload(): JWTPayload | null {
  const token = getToken()

  if (!token) {
    return null
  }

  try {
    return jwtDecode<JWTPayload>(token)
  } catch (error) {
    console.error('Failed to decode JWT token:', error)
    return null
  }
}

/**
 * Check if token is expired (internal helper)
 */
function isTokenExpired(payload: JWTPayload): boolean {
  const currentTime = Date.now() / 1000
  return payload.exp < currentTime
}

/**
 * Check if user is authenticated (token exists and not expired)
 */
export function isAuthenticated(): boolean {
  const payload = getTokenPayload()

  if (!payload) {
    return false
  }

  return !isTokenExpired(payload)
}

/**
 * Check if user has a specific role
 */
export function hasRole(role: string): boolean {
  const payload = getTokenPayload()

  if (!payload || !payload.roles) {
    return false
  }

  return payload.roles.includes(role)
}
