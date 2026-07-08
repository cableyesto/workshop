import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'

import EmployeeTable from '@/components/EmployeeTable.vue'
import type { Mechanic } from '@/types/employee'

// Mock formatDate utility
vi.mock('@/utils/date', () => ({
  formatDate: vi.fn((date) => `formatted-${date}`),
}))

// Test data
const mockEmployees: Mechanic[] = [
  {
    id: 1,
    lastName: 'Dupont',
    firstName: 'Jean',
    birthDate: '1990-05-15',
    hireDate: '2020-01-10',
  },
  {
    id: 2,
    lastName: 'Martin',
    firstName: 'Marie',
    birthDate: '1985-08-22',
    hireDate: '2019-03-15',
  },
]

// Stubs for Table UI components
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
  TableCell: {
    template: '<td><slot /></td>',
  },
  TableHead: {
    template: '<th><slot /></th>',
  },
}

describe('EmployeeTable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders table with headers when no employees', () => {
    render(EmployeeTable, {
      props: {
        employees: [],
        type: 'mechanic',
      },
      global: { stubs },
    })

    // Verify all headers are present
    expect(screen.getByText('Nom')).toBeInTheDocument()
    expect(screen.getByText('Prénom')).toBeInTheDocument()
    expect(screen.getByText('Date de naissance')).toBeInTheDocument()
    expect(screen.getByText('Date de démarrage')).toBeInTheDocument()
    expect(screen.getByText('Modification')).toBeInTheDocument()
  })

  it('renders table rows for each employee', () => {
    render(EmployeeTable, {
      props: {
        employees: mockEmployees,
        type: 'mechanic',
      },
      global: { stubs },
    })

    // Verify employee data is displayed
    expect(screen.getByText('Dupont')).toBeInTheDocument()
    expect(screen.getByText('Jean')).toBeInTheDocument()
    expect(screen.getByText('Martin')).toBeInTheDocument()
    expect(screen.getByText('Marie')).toBeInTheDocument()
  })

  it('formats dates correctly using formatDate', async () => {
    const { formatDate } = await import('@/utils/date')

    render(EmployeeTable, {
      props: {
        employees: mockEmployees,
        type: 'mechanic',
      },
      global: { stubs },
    })

    // Verify formatDate was called for birthDate and hireDate of each employee
    expect(formatDate).toHaveBeenCalledWith('1990-05-15')
    expect(formatDate).toHaveBeenCalledWith('2020-01-10')
    expect(formatDate).toHaveBeenCalledWith('1985-08-22')
    expect(formatDate).toHaveBeenCalledWith('2019-03-15')

    // Verify formatted dates are displayed
    expect(screen.getByText('formatted-1990-05-15')).toBeInTheDocument()
    expect(screen.getByText('formatted-2020-01-10')).toBeInTheDocument()
  })

  it('emits update event when button clicked', async () => {
    const user = userEvent.setup()
    const { emitted } = render(EmployeeTable, {
      props: {
        employees: mockEmployees,
        type: 'mechanic',
      },
      global: { stubs },
    })

    // Find and click first "Actualiser" button
    const buttons = screen.getAllByRole('button', { name: /actualiser/i })
    await user.click(buttons[0]!)

    // Verify update event was emitted with correct employee
    const updateEvents = emitted().update
    expect(updateEvents).toBeTruthy()
    expect(updateEvents).toHaveLength(1)
    expect(updateEvents![0]).toEqual([mockEmployees[0]])
  })

  it('renders update button for each employee', () => {
    render(EmployeeTable, {
      props: {
        employees: mockEmployees,
        type: 'mechanic',
      },
      global: { stubs },
    })

    // Verify one button per employee
    const buttons = screen.getAllByRole('button', { name: /actualiser/i })
    expect(buttons).toHaveLength(2)
  })
})
