import { describe, it, expect, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import userEvent from '@testing-library/user-event'
import CarStorageTable from '@/components/cars/CarStorageTable.vue'
import type { StoredCar } from '@/types/cars'

// Stubs for Table components
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
}

describe('CarStorageTable.vue', () => {
  const mockCars: StoredCar[] = [
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
    },
  ]

  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
  })

  describe('Rendering', () => {
    it('renders table headers', () => {
      render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Marque')).toBeInTheDocument()
      expect(screen.getByText('Modèle')).toBeInTheDocument()
      expect(screen.getByText('Couleur')).toBeInTheDocument()
      expect(screen.getByText('Plaque')).toBeInTheDocument()
      expect(screen.getByText('Fiche client')).toBeInTheDocument()
    })

    it('renders without errors with empty cars array', () => {
      const { container } = render(CarStorageTable, {
        props: {
          cars: [],
        },
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders all cars in the array', () => {
      render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Renault')).toBeInTheDocument()
      expect(screen.getByText('Peugeot')).toBeInTheDocument()
    })
  })

  describe('Car data display', () => {
    it('displays car manufacturer', () => {
      render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Renault')).toBeInTheDocument()
      expect(screen.getByText('Peugeot')).toBeInTheDocument()
    })

    it('displays car model', () => {
      render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Clio')).toBeInTheDocument()
      expect(screen.getByText('208')).toBeInTheDocument()
    })

    it('displays car color', () => {
      render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Bleu')).toBeInTheDocument()
      expect(screen.getByText('Rouge')).toBeInTheDocument()
    })

    it('displays car license plate', () => {
      render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('AB-123-CD')).toBeInTheDocument()
      expect(screen.getByText('XY-789-ZW')).toBeInTheDocument()
    })
  })

  describe('Client information', () => {
    it('displays client name as button', () => {
      render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText(/Dupont/)).toBeInTheDocument()
      expect(screen.getByText(/Jean/)).toBeInTheDocument()
      expect(screen.getByText(/Martin/)).toBeInTheDocument()
      expect(screen.getByText(/Marie/)).toBeInTheDocument()
    })

    it('client name button is clickable', () => {
      const { container } = render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      const buttons = container.querySelectorAll('button')
      expect(buttons.length).toBe(2)
    })

    it('emits viewClient event when client button is clicked', async () => {
      const user = userEvent.setup()
      const { emitted, container } = render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      const buttons = container.querySelectorAll('button')
      await user.click(buttons[0]!)

      expect(emitted().viewClient).toBeTruthy()
      expect(emitted().viewClient?.[0]).toEqual([mockCars[0]!.client])
    })
  })

  describe('Multiple cars', () => {
    it('renders correct number of rows', () => {
      const { container } = render(CarStorageTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      const tbody = container.querySelector('tbody')
      const rows = tbody?.querySelectorAll('tr')
      expect(rows?.length).toBe(2)
    })

    it('handles many cars', () => {
      const manyCars = Array.from(
        { length: 10 },
        (_, i): StoredCar => ({
          ...mockCars[0]!,
          id: i + 1,
          licensePlate: `AB-${100 + i}-CD`,
        }),
      )

      const { container } = render(CarStorageTable, {
        props: {
          cars: manyCars,
        },
        global: { stubs },
      })

      const tbody = container.querySelector('tbody')
      const rows = tbody?.querySelectorAll('tr')
      expect(rows?.length).toBe(10)
    })
  })

  describe('Edge cases', () => {
    it('handles single car', () => {
      const { container } = render(CarStorageTable, {
        props: {
          cars: [mockCars[0]!],
        },
        global: { stubs },
      })

      const tbody = container.querySelector('tbody')
      const rows = tbody?.querySelectorAll('tr')
      expect(rows?.length).toBe(1)
    })
  })
})
