import type { Client } from './client'

export interface StoredCar {
  id: number
  manufacturer: string
  model: string
  licensePlate: string
  color: string
  client: Client
}

export interface Car extends StoredCar {
  registrationYear: number | null
  registrationMonth: number | null
  mileage: number | null
  isStored: boolean
}

export interface CarWithIntervention extends StoredCar {
  intervention: {
    status: 'affectee' | 'en_cours' | 'en_pause' | 'terminee'
  }
}
