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
  refresh_token: string
}

export interface User {
  id: number
  email: string
  roles: string[]
  garage_id?: number
  garage_siret?: string
  garage_slug?: string
}
