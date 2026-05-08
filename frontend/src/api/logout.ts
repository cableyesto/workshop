/**
 * Logout - clear JWT from localStorage
 */
export function logout(): void {
  localStorage.removeItem('jwt_token')
}
