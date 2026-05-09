export interface Client {
  id: number
  firstName: string
  lastName: string
  email: string
  phone?: string
  isClientCalledBack: boolean
}

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
