import { clearToken } from '../utils/auth'

/**
 * Logout - clear JWT from localStorage
 */
export function logout(): void {
  clearToken()
}
