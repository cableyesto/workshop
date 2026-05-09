import type { Client } from './client'

export interface StoredCar {
  id: number
  manufacturer: string
  model: string
  licensePlate: string
  color: string
  client: Client
}

export interface CarWithIntervention extends StoredCar {
  intervention: {
    status: 'affectee' | 'en_cours' | 'en_pause' | 'terminee'
  }
}
