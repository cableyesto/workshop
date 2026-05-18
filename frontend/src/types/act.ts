// Act retrieved from backend (has ID)
export interface Act {
  id: number
  name: string
  quantity: number
  unitPrice: number
}

// Act payload for mutations (create/update)
export interface ActPayload {
  name: string
  quantity: number
  unitPrice: number
}
