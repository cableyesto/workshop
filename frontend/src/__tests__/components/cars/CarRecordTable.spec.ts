import { describe, it, expect, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import userEvent from '@testing-library/user-event'
import CarRecordTable from '@/components/cars/CarRecordTable.vue'
import type { Car } from '@/types/cars'

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

describe('CarRecordTable.vue', () => {
  const mockCars: Car[] = [
    {
      id: 1,
      manufacturer: 'Renault',
      model: 'Clio',
      color: 'Bleu',
      licensePlate: 'AB-123-CD',
      registrationYear: 2020,
      registrationMonth: 6,
      mileage: 50000,
      isStored: false,
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
      registrationYear: 2021,
      registrationMonth: 3,
      mileage: 30000,
      isStored: true,
      client: {
        id: 2,
        firstName: 'Marie',
        lastName: 'Martin',
        email: 'marie@example.com',
        phone: '0698765432',
        isClientCalledBack: false,
      },
    },
  ]

  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
  })

  describe('Rendering', () => {
    it('renders table headers', () => {
      render(CarRecordTable, {
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
      expect(screen.getByText('Modification')).toBeInTheDocument()
      expect(screen.getByText('Ajout au dépôt')).toBeInTheDocument()
    })

    it('renders without errors with empty cars array', () => {
      const { container } = render(CarRecordTable, {
        props: {
          cars: [],
        },
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders all cars in the array', () => {
      render(CarRecordTable, {
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
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Renault')).toBeInTheDocument()
      expect(screen.getByText('Peugeot')).toBeInTheDocument()
    })

    it('displays car model', () => {
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Clio')).toBeInTheDocument()
      expect(screen.getByText('208')).toBeInTheDocument()
    })

    it('displays car color', () => {
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Bleu')).toBeInTheDocument()
      expect(screen.getByText('Rouge')).toBeInTheDocument()
    })

    it('displays car license plate', () => {
      render(CarRecordTable, {
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
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Dupont Jean')).toBeInTheDocument()
      expect(screen.getByText('Martin Marie')).toBeInTheDocument()
    })

    it('client name button is clickable', () => {
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      const clientButton = screen.getByText('Dupont Jean')
      expect(clientButton.tagName).toBe('BUTTON')
    })

    it('emits viewClient event when client button is clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      await user.click(screen.getByText('Dupont Jean'))

      expect(emitted().viewClient).toBeTruthy()
      expect(emitted().viewClient?.[0]).toEqual([mockCars[0]!.client])
    })
  })

  describe('Edit functionality', () => {
    it('displays edit button for each car', () => {
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      const editButtons = screen.getAllByText('Actualiser')
      expect(editButtons).toHaveLength(2)
    })

    it('emits editCar event when edit button is clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      const editButtons = screen.getAllByText('Actualiser')
      await user.click(editButtons[0]!)

      expect(emitted().editCar).toBeTruthy()
      expect(emitted().editCar?.[0]).toEqual([mockCars[0]])
    })
  })

  describe('Storage functionality', () => {
    it('displays "Ajouter" button for cars not in storage', () => {
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Ajouter')).toBeInTheDocument()
    })

    it('displays "Au dépôt" text for cars in storage', () => {
      render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      expect(screen.getByText('Au dépôt')).toBeInTheDocument()
    })

    it('emits addToStorage event when add button is clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(CarRecordTable, {
        props: {
          cars: mockCars,
        },
        global: { stubs },
      })

      await user.click(screen.getByText('Ajouter'))

      expect(emitted().addToStorage).toBeTruthy()
      expect(emitted().addToStorage?.[0]).toEqual([mockCars[0]!.licensePlate])
    })

    it('does not show add button for stored cars', () => {
      render(CarRecordTable, {
        props: {
          cars: [mockCars[1]], // Only the stored car
        },
        global: { stubs },
      })

      expect(screen.queryByText('Ajouter')).not.toBeInTheDocument()
      expect(screen.getByText('Au dépôt')).toBeInTheDocument()
    })
  })

  describe('Multiple cars', () => {
    it('renders correct number of rows', () => {
      const { container } = render(CarRecordTable, {
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
        (_, i): Car => ({
          ...mockCars[0]!,
          id: i + 1,
          licensePlate: `AB-${100 + i}-CD`,
        }),
      )

      const { container } = render(CarRecordTable, {
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
    it('handles car with minimal data', () => {
      const minimalCar: Car = {
        ...mockCars[0]!,
        registrationYear: null,
        registrationMonth: null,
        mileage: null,
      }

      expect(() => {
        render(CarRecordTable, {
          props: {
            cars: [minimalCar],
          },
          global: { stubs },
        })
      }).not.toThrow()
    })
  })
})
