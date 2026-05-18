// Task retrieved from backend (has ID)
export interface Task {
  id: number
  name: string
  quantity: number
  unitPrice: number
}

// Task payload for mutations (create/update)
export interface TaskPayload {
  name: string
  quantity: number
  unitPrice: number
}
