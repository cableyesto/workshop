// Backend enum types (used in code, API calls)
export type InterventionType = 'Repair' | 'Diagnostic'
export type DocumentType = 'Estimate' | 'Invoice'
export type InterventionStatus = 'Assigned' | 'In Progress' | 'Paused' | 'Stopped'

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
  date: string
  startTime: string
  status: InterventionStatus
  interventionType: InterventionType | null
  documentType: DocumentType | null
  clientRemark: boolean
  clientRequest: string | null
  interventionEndRemark: boolean
  finalNote: string | null
}

export interface SearchInterventionResponse {
  found: boolean
  intervention?: Intervention
}

// Payload for updating intervention
export interface UpdateInterventionPayload {
  interventionType?: InterventionType
  documentType?: DocumentType
  mechanicId?: number
  licensePlate?: string
  date?: string
  startTime?: string
  clientRequest?: string
  finalNote?: string
}
