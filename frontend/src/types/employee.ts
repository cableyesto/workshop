export interface Mechanic {
  id: number
  lastName: string
  firstName: string
  birthDate: string // ISO date format
  hireDate: string // ISO date format
}

export interface Receptionist {
  id: number
  lastName: string
  firstName: string
  birthDate: string // ISO date format
  hireDate: string // ISO date format
  email: string
}

export interface EmployeeFormData {
  type: 'mechanic' | 'receptionist'
  lastName: string
  firstName: string
  birthDate: string // YYYY-MM-DD format from input type="date" - required
  hireDate?: string // YYYY-MM-DD format from input type="date" - optional
  email?: string
  password?: string
}
