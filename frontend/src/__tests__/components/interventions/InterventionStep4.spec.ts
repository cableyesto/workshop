import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'
import { ref } from 'vue'

import InterventionStep4 from '@/components/interventions/InterventionStep4.vue'
import * as interventionTasksApi from '@/api/intervention-tasks'

// Mock API module
vi.mock('@/api/intervention-tasks', () => ({
  useInterventionTasksQuery: vi.fn(),
  useCreateInterventionTaskMutation: vi.fn(),
  useUpdateInterventionTaskMutation: vi.fn(),
}))

// Mock TaskDialog component
const mockTaskDialog = {
  name: 'TaskDialog',
  template: '<div data-testid="task-dialog"><slot /></div>',
  props: ['open', 'mode', 'task'],
}

// Mock task data
const mockTasks = [
  {
    id: 1,
    name: 'Vidange moteur',
    quantity: 1,
    unitPrice: 45.5,
  },
  {
    id: 2,
    name: 'Changement plaquettes',
    quantity: 1,
    unitPrice: 120.0,
  },
]

// Stubs for UI components
const stubs = {
  Button: {
    template: '<button :type="type"><slot /></button>',
    props: ['type', 'variant'],
  },
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
  TaskDialog: mockTaskDialog,
  Breadcrumb: {
    template: '<nav><slot /></nav>',
  },
  BreadcrumbList: {
    template: '<ol><slot /></ol>',
  },
  BreadcrumbItem: {
    template: '<li><slot /></li>',
  },
  BreadcrumbPage: {
    template: '<span><slot /></span>',
  },
  BreadcrumbSeparator: {
    template: '<span>/</span>',
  },
}

describe('InterventionStep4', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    // Mock tasks query
    vi.mocked(interventionTasksApi.useInterventionTasksQuery).mockReturnValue({
      data: ref(null),
      isLoading: ref(false),
      error: ref(null),
      status: ref('success'),
      state: ref({} as any),
      asyncStatus: ref('success'),
      refetch: vi.fn(),
      refresh: vi.fn(),
    } as any)

    // Mock create mutation
    vi.mocked(interventionTasksApi.useCreateInterventionTaskMutation).mockReturnValue({
      mutate: vi.fn(),
      isPending: ref(false),
      error: ref(null),
    } as any)

    // Mock update mutation
    vi.mocked(interventionTasksApi.useUpdateInterventionTaskMutation).mockReturnValue({
      mutate: vi.fn(),
      isPending: ref(false),
      error: ref(null),
    } as any)
  })

  describe('Rendering', () => {
    it('renders step 4 title', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Étape 4: Actes')).toBeInTheDocument()
    })

    it('renders breadcrumb navigation', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Type')).toBeInTheDocument()
      expect(screen.getByText('Remarque')).toBeInTheDocument()
      // "Acte" appears multiple times (breadcrumb + table header)
      const acteElements = screen.getAllByText('Acte')
      expect(acteElements.length).toBeGreaterThan(0)
    })

    it('renders add task button', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Ajouter un acte')).toBeInTheDocument()
    })

    it('renders tasks table', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      // Table headers
      expect(screen.getAllByText('Acte')[0]).toBeInTheDocument()
      expect(screen.getByText('Quantité')).toBeInTheDocument()
      expect(screen.getByText('Prix unitaire')).toBeInTheDocument()
      expect(screen.getByText('Editer')).toBeInTheDocument()
    })

    it('displays empty table message when no tasks', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Aucun acte ajouté')).toBeInTheDocument()
    })
  })

  describe('Tasks Display', () => {
    it('displays tasks when data is loaded', () => {
      vi.mocked(interventionTasksApi.useInterventionTasksQuery).mockReturnValue({
        data: ref(mockTasks),
        isLoading: ref(false),
        error: ref(null),
        status: ref('success'),
        state: ref({} as any),
        asyncStatus: ref('success'),
        refetch: vi.fn(),
        refresh: vi.fn(),
      } as any)

      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: true,
        },
        global: { stubs },
      })

      // Task names
      expect(screen.getByText('Vidange moteur')).toBeInTheDocument()
      expect(screen.getByText('Changement plaquettes')).toBeInTheDocument()
    })
  })

  describe('Navigation Buttons', () => {
    it('renders all navigation buttons', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Précédent')).toBeInTheDocument()
      expect(screen.getByText('Valider')).toBeInTheDocument()
    })

    it('emits back event when back button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      const backButton = screen.getByText('Précédent')
      await user.click(backButton)

      expect(emitted().back).toBeTruthy()
    })

    it('emits next event when validate button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      const validateButton = screen.getByText('Valider')
      await user.click(validateButton)

      expect(emitted().next).toBeTruthy()
    })
  })

  describe('Props', () => {
    it('accepts interventionId prop', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 123,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Étape 4: Actes')).toBeInTheDocument()
    })

    it('accepts isEditMode prop', () => {
      render(InterventionStep4, {
        props: {
          interventionId: 1,
          isEditMode: true,
        },
        global: { stubs },
      })

      expect(screen.getByText('Étape 4: Actes')).toBeInTheDocument()
    })
  })
})
