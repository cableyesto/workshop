import { describe, it, expect, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import userEvent from '@testing-library/user-event'
import CarRestitutionTable from '@/components/cars/CarRestitutionTable.vue'
import type { CarWithIntervention } from '@/types/cars'

// Stubs for UI components
const stubs = {
  Table: {
    template: '<table><slot /></table>',
  },
  TableHeader: {
    template: '<thead><slot /></thead>',
  },
  TableBody: {
    template: '<tbody><slot /></tbody>',
  },
  TableRow: {
    template: '<tr><slot /></tr>',
  },
  TableHead: {
    template: '<th><slot /></th>',
  },
  TableCell: {
    template: '<td><slot /></td>',
  },
  Checkbox: {
    template:
      '<input type="checkbox" :checked="modelValue" :disabled="disabled" @change="$emit(\'update:modelValue\', !modelValue)" />',
    props: ['modelValue', 'disabled'],
  },
}

describe('CarRestitutionTable.vue', () => {
  const mockCars: CarWithIntervention[] = [
    {
      id: 1,
      manufacturer: 'Renault',
      model: 'Clio',
      color: 'Bleu',
      licensePlate: 'AB-123-CD',
      client: {
        id: 1,
        firstName: 'Jean',
        lastName: 'Dupont',
        email: 'jean@example.com',
        phone: '0612345678',
        isClientCalledBack: false,
      },
      intervention: {
        globalStatus: 'Completed',
      },
    },
    {
      id: 2,
      manufacturer: 'Peugeot',
      model: '208',
      color: 'Rouge',
      licensePlate: 'XY-789-ZW',
      client: {
        id: 2,
        firstName: 'Marie',
        lastName: 'Martin',
        email: 'marie@example.com',
        phone: '0698765432',
        isClientCalledBack: true,
      },
      intervention: {
        globalStatus: 'Completed',
      },
    },
  ]

  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
  })

  describe('Rendering', () => {
    it('renders table headers', () => {
      render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      expect(screen.getByText('Marque')).toBeInTheDocument()
      expect(screen.getByText('Modèle')).toBeInTheDocument()
      expect(screen.getByText('Couleur')).toBeInTheDocument()
      expect(screen.getByText('Plaque')).toBeInTheDocument()
      expect(screen.getByText('Fiche client')).toBeInTheDocument()
      expect(screen.getByText('Client rappelé')).toBeInTheDocument()
      expect(screen.getByText('Action')).toBeInTheDocument()
    })

    it('renders without errors with empty cars array', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: [],
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders all cars', () => {
      render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      expect(screen.getByText('Renault')).toBeInTheDocument()
      expect(screen.getByText('Peugeot')).toBeInTheDocument()
    })
  })

  describe('Car data display', () => {
    it('displays car manufacturer', () => {
      render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      expect(screen.getByText('Renault')).toBeInTheDocument()
      expect(screen.getByText('Peugeot')).toBeInTheDocument()
    })

    it('displays car model', () => {
      render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      expect(screen.getByText('Clio')).toBeInTheDocument()
      expect(screen.getByText('208')).toBeInTheDocument()
    })

    it('displays car license plate', () => {
      render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      expect(screen.getByText('AB-123-CD')).toBeInTheDocument()
      expect(screen.getByText('XY-789-ZW')).toBeInTheDocument()
    })
  })

  describe('Client information', () => {
    it('displays client names as buttons', () => {
      render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      expect(screen.getByText('Dupont Jean')).toBeInTheDocument()
      expect(screen.getByText('Martin Marie')).toBeInTheDocument()
    })

    it('emits viewClient when client button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      await user.click(screen.getByText('Dupont Jean'))

      expect(emitted().viewClient).toBeTruthy()
      expect(emitted().viewClient?.[0]).toEqual([mockCars[0]!.client])
    })
  })

  describe('Client called back checkbox', () => {
    it('renders checkboxes for each car', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const checkboxes = container.querySelectorAll('input[type="checkbox"]')
      expect(checkboxes.length).toBe(2)
    })

    it('checkbox reflects isClientCalledBack state', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const checkboxes = container.querySelectorAll('input[type="checkbox"]')
      expect(checkboxes[0]).not.toBeChecked()
      expect(checkboxes[1]).toBeChecked()
    })

    it('emits toggleCalledBack when checkbox clicked', async () => {
      const user = userEvent.setup()
      const { emitted, container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const checkboxes = container.querySelectorAll('input[type="checkbox"]')
      await user.click(checkboxes[0]!)

      expect(emitted().toggleCalledBack).toBeTruthy()
      expect(emitted().toggleCalledBack?.[0]).toEqual([1, false])
    })

    it('checkbox is disabled when client is being updated', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set([1]),
        },
        global: { stubs },
      })

      const checkboxes = container.querySelectorAll('input[type="checkbox"]')
      expect(checkboxes[0]).toBeDisabled() // Disabled because updatingClientIds has ID 1
      expect(checkboxes[1]).toBeDisabled() // Disabled because isClientCalledBack is true
    })

    it('checkbox is disabled when client already called back', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const checkboxes = container.querySelectorAll('input[type="checkbox"]')
      expect(checkboxes[1]).toBeDisabled() // Second car has isClientCalledBack: true
    })
  })

  describe('Remove from storage', () => {
    it('displays "Retirer" button for each car', () => {
      render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const removeButtons = screen.getAllByText('Retirer')
      expect(removeButtons).toHaveLength(2)
    })

    it('emits removeFromStorage when button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const removeButtons = screen.getAllByText('Retirer')
      await user.click(removeButtons[0]!)

      expect(emitted().removeFromStorage).toBeTruthy()
      expect(emitted().removeFromStorage?.[0]).toEqual([1])
    })
  })

  describe('Multiple cars', () => {
    it('renders correct number of rows', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const tbody = container.querySelector('tbody')
      const rows = tbody?.querySelectorAll('tr')
      expect(rows?.length).toBe(2)
    })
  })

  describe('UpdatingClientIds Set', () => {
    it('handles multiple updating clients', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set([1, 2]),
        },
        global: { stubs },
      })

      const checkboxes = container.querySelectorAll('input[type="checkbox"]')
      expect(checkboxes[0]).toBeDisabled()
      expect(checkboxes[1]).toBeDisabled()
    })

    it('handles empty updatingClientIds set', () => {
      const { container } = render(CarRestitutionTable, {
        props: {
          cars: mockCars,
          updatingClientIds: new Set(),
        },
        global: { stubs },
      })

      const checkboxes = container.querySelectorAll('input[type="checkbox"]')
      // First checkbox should not be disabled by updatingClientIds
      // (but second is disabled because isClientCalledBack is true)
      expect(checkboxes[0]).not.toBeDisabled()
    })
  })
})
