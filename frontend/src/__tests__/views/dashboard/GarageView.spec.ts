import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen } from '@testing-library/vue'
import { ref } from 'vue'
import '@testing-library/jest-dom'
import GarageView from '../../../views/dashboard/GarageView.vue'
import type { Garage } from '../../../types'

// Mock the garage API
vi.mock('../../../api/garage', () => ({
  useGarageQuery: vi.fn(),
}))

import { useGarageQuery } from '../../../api/garage'

// Helper function to mock garage query with reactive refs
function mockGarageQuery(garageData: Garage) {
  vi.mocked(useGarageQuery).mockReturnValue({
    data: ref(garageData),
    isLoading: ref(false),
    error: ref(null),
  } as any)
}

describe('GarageView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    // Mock localStorage with JWT token
    localStorage.setItem(
      'jwt_token',
      'header.' + btoa(JSON.stringify({ garage_id: 1 })) + '.signature',
    )
  })

  it('renders h1 title with correct garage name', () => {
    const mockGarage: Garage = {
      id: 1,
      name: 'Garage Test SAS',
      siret: '12345678901234',
      timeSlots: [],
    }

    mockGarageQuery(mockGarage)
    render(GarageView)

    const heading = screen.getByRole('heading', { level: 1 })
    expect(heading).toHaveTextContent('Bienvenue chez Garage Test SAS')
  })

  it('displays "Fermé" for 5 days when only 2 days are open', () => {
    const mockGarage: Garage = {
      id: 2,
      name: 'Garage Deux Jours',
      siret: '98765432109876',
      timeSlots: [
        { dayOfWeek: 'Lundi', startTime: '09:00', endTime: '18:00' },
        { dayOfWeek: 'Mercredi', startTime: '10:00', endTime: '17:00' },
      ],
    }

    mockGarageQuery(mockGarage)
    render(GarageView)

    const closedElements = screen.getAllByText('Fermé')
    expect(closedElements).toHaveLength(5)
  })

  it('splits continuous slot (9h-17h) and displays separate slots correctly', () => {
    const mockGarage: Garage = {
      id: 3,
      name: 'Garage Split Test',
      siret: '11111111111111',
      timeSlots: [
        { dayOfWeek: 'Mardi', startTime: '09:00', endTime: '17:00' }, // Should split
        { dayOfWeek: 'Jeudi', startTime: '09:00', endTime: '13:15' }, // No split
        { dayOfWeek: 'Jeudi', startTime: '14:45', endTime: '19:00' }, // No split
      ],
    }

    mockGarageQuery(mockGarage)
    render(GarageView)

    // Mardi: Should be split with 12h00 and 13h00
    expect(screen.getByText(/Mardi/)).toBeInTheDocument()
    expect(screen.getByText(/09h00 - 12h00 \/ 13h00 - 17h00/)).toBeInTheDocument()

    // Jeudi: Should display both slots as-is
    expect(screen.getByText(/Jeudi/)).toBeInTheDocument()
    expect(screen.getByText(/09h00 - 13h15 \/ 14h45 - 19h00/)).toBeInTheDocument()
  })

  it('displays only one "Fermé" for full week schedule (Sunday closed)', () => {
    const mockGarage: Garage = {
      id: 24,
      name: 'Samson',
      siret: '94389903588903',
      timeSlots: [
        { dayOfWeek: 'Lundi', startTime: '09:00', endTime: '17:00' },
        { dayOfWeek: 'Mardi', startTime: '09:00', endTime: '17:00' },
        { dayOfWeek: 'Mercredi', startTime: '09:00', endTime: '17:00' },
        { dayOfWeek: 'Jeudi', startTime: '09:00', endTime: '17:00' },
        { dayOfWeek: 'Vendredi', startTime: '09:00', endTime: '17:00' },
        { dayOfWeek: 'Samedi', startTime: '09:00', endTime: '17:00' },
      ],
    }

    mockGarageQuery(mockGarage)
    render(GarageView)

    const fermeElements = screen.getAllByText('Fermé')
    expect(fermeElements).toHaveLength(1)

    expect(screen.getByText(/Dimanche/)).toBeInTheDocument()
  })
})
