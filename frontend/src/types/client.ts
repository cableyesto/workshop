export interface Client {
  id: number
  firstName: string
  lastName: string
  email: string | null
  phone: string
  isClientCalledBack: boolean
}

export interface UpdateClientData {
  firstName: string
  lastName: string
  email: string | null
  phone: string
}
