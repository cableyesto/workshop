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

// Aggregated status across all interventions for a car
export type CarStatus = 'Active' | 'Paused' | 'Assigned' | 'Completed'

export interface CarWithIntervention extends StoredCar {
  intervention: {
    globalStatus: CarStatus
  }
}
