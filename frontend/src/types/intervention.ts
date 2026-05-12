export interface Intervention {
  id: number
  mechanic: {
    id: number
    firstName: string
    lastName: string
  }
  car: {
    id: number
    licensePlate: string
  }
  startDate?: string
  startTime?: string
  status: 'Affectée' | 'En cours' | 'En pause' | 'Terminée'
  interventionType?: 'Réparation' | 'Diagnostic' | null
  documentType?: 'Devis' | 'Facture' | null
  clientRemark?: boolean
  clientNeed?: string | null
  interventionEndRemark?: boolean
  interventionEndNote?: string | null
}

export interface SearchInterventionResponse {
  found: boolean
  intervention?: Intervention
}
