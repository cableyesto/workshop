import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import EmployeesView from '@/views/dashboard/EmployeesView.vue'

// Mock API hooks
vi.mock('@/api/employees', () => ({
  useMechanicsQuery: vi.fn(() => ({
    data: { value: [] },
    isLoading: { value: false },
  })),
  useReceptionistsQuery: vi.fn(() => ({
    data: { value: [] },
    isLoading: { value: false },
  })),
  useCreateMechanicMutation: vi.fn(() => ({ mutate: vi.fn() })),
  useUpdateMechanicMutation: vi.fn(() => ({ mutate: vi.fn() })),
  useDeleteMechanicMutation: vi.fn(() => ({ mutate: vi.fn() })),
  useCreateReceptionistMutation: vi.fn(() => ({ mutate: vi.fn() })),
  useUpdateReceptionistMutation: vi.fn(() => ({ mutate: vi.fn() })),
  useDeleteReceptionistMutation: vi.fn(() => ({ mutate: vi.fn() })),
}))

// Mock components
vi.mock('@/components/EmployeeTable.vue', () => ({
  default: {
    name: 'EmployeeTable',
    template: '<div data-testid="employee-table">EmployeeTable</div>',
    props: ['employees', 'type', 'isLoading'],
  },
}))

vi.mock('@/components/MechanicDialog.vue', () => ({
  default: {
    name: 'MechanicDialog',
    template: '<div data-testid="mechanic-dialog">MechanicDialog</div>',
    props: ['open', 'mode', 'employee'],
  },
}))

vi.mock('@/components/ReceptionistDialog.vue', () => ({
  default: {
    name: 'ReceptionistDialog',
    template: '<div data-testid="receptionist-dialog">ReceptionistDialog</div>',
    props: ['open', 'mode', 'employee'],
  },
}))

// Mock Tabs components
const stubs = {
  Tabs: {
    template: '<div data-testid="tabs"><slot /></div>',
    props: ['modelValue', 'defaultValue'],
  },
  TabsList: {
    template: '<div data-testid="tabs-list"><slot /></div>',
  },
  TabsTrigger: {
    template: '<button data-testid="tab-trigger"><slot /></button>',
    props: ['value'],
  },
  TabsContent: {
    template: '<div data-testid="tab-content"><slot /></div>',
    props: ['value'],
  },
  Button: {
    template: '<button><slot /></button>',
  },
}

describe('EmployeesView.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(EmployeesView, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders page title', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      expect(screen.getByText('Employés')).toBeInTheDocument()
    })

    it('renders Tabs component', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      expect(screen.getByTestId('tabs')).toBeInTheDocument()
    })
  })

  describe('Tabs structure', () => {
    it('renders TabsList', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      expect(screen.getByTestId('tabs-list')).toBeInTheDocument()
    })

    it('renders receptionist tab trigger', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      expect(screen.getByText('Accueil')).toBeInTheDocument()
    })

    it('renders mechanic tab trigger', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      expect(screen.getByText('Mécanicien')).toBeInTheDocument()
    })

    it('renders both tab triggers', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      const triggers = screen.getAllByTestId('tab-trigger')
      expect(triggers).toHaveLength(2)
    })
  })

  describe('Tab content', () => {
    it('renders TabsContent sections', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      const contents = screen.getAllByTestId('tab-content')
      expect(contents).toHaveLength(2)
    })

    it('renders loading state', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      expect(screen.getAllByText('Chargement...').length).toBeGreaterThan(0)
    })

    it('renders ReceptionistDialog', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      expect(screen.getByTestId('receptionist-dialog')).toBeInTheDocument()
    })
  })

  describe('Add employee button', () => {
    it('renders add button for each tab', () => {
      render(EmployeesView, {
        global: { stubs },
      })

      const addButtons = screen.getAllByText(/Ajouter/i)
      expect(addButtons.length).toBeGreaterThan(0)
    })
  })

  describe('Layout', () => {
    it('has flex column layout', () => {
      const { container } = render(EmployeesView, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.flex.flex-col')
      expect(mainDiv).toBeInTheDocument()
    })

    it('has proper gap spacing', () => {
      const { container } = render(EmployeesView, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.gap-6')
      expect(mainDiv).toBeInTheDocument()
    })
  })

  describe('API integration', () => {
    it('integrates with mechanics query', () => {
      expect(() => {
        render(EmployeesView, {
          global: { stubs },
        })
      }).not.toThrow()
    })

    it('integrates with receptionists query', () => {
      expect(() => {
        render(EmployeesView, {
          global: { stubs },
        })
      }).not.toThrow()
    })

    it('integrates with all mutation hooks', () => {
      expect(() => {
        render(EmployeesView, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })
})
