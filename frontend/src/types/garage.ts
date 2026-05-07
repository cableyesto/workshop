export interface TimeSlot {
  dayOfWeek: string // "Lundi", "Mardi", "Mercredi", etc.
  startTime: string // Format: "09:00"
  endTime: string // Format: "17:00"
}

export interface Garage {
  id: number
  name: string
  siret: string
  timeSlots: TimeSlot[]
}
