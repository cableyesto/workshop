export interface LoginOwnerRequest {
  email: string
  password: string
}

export interface LoginReceptionistRequest {
  siret: string
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  // refresh_token is in HttpOnly cookie, not in response body
}

export interface User {
  id: number
  email: string
  roles: string[]
  garage_id?: number
  garage_siret?: string
  garage_slug?: string
}

export interface JWTPayload {
  exp: number
  iat: number
  roles: string[]
  username: string
  garage_id?: number // For receptionist
  garage_ids?: number[] // For owner
  garage_siret?: string // For receptionist
}
