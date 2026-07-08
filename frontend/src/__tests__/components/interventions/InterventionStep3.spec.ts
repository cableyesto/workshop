import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'
import { ref } from 'vue'

import InterventionStep3 from '@/components/interventions/InterventionStep3.vue'
import * as interventionsApi from '@/api/interventions'

// Mock API module
vi.mock('@/api/interventions', () => ({
  useInterventionQuery: vi.fn(),
  useUpdateInterventionMutation: vi.fn(),
}))

// Mock vee-validate
vi.mock('vee-validate', () => ({
  useForm: vi.fn(() => ({
    handleSubmit: vi.fn((onSuccess) => (e: Event) => {
      e?.preventDefault?.()
      onSuccess({
        hasClientRemark: 'false',
        clientRequest: '',
        hasInterventionEndRemark: 'false',
        finalNote: '',
      })
    }),
    defineField: vi.fn((name) => [
      { value: '' },
      { name, onBlur: vi.fn(), onChange: vi.fn() },
    ]),
    setValues: vi.fn(),
  })),
}))

vi.mock('@vee-validate/zod', () => ({
  toTypedSchema: vi.fn((schema) => schema),
}))

// Mock intervention data
const mockIntervention = {
  id: 1,
  mechanic: {
    id: 1,
    firstName: 'Jean',
    lastName: 'Dupont',
  },
  car: {
    id: 1,
    licensePlate: 'AB-123-CD',
  },
  date: '2024-01-15',
  startTime: '10:30',
  status: 'In Progress' as const,
  interventionType: 'Repair' as const,
  documentType: 'Estimate' as const,
  clientRemark: true,
  clientRequest: 'Bruit au freinage',
  interventionEndRemark: false,
  finalNote: null,
}

// Stubs for UI components
const stubs = {
  Button: {
    template: '<button :type="type"><slot /></button>',
    props: ['type', 'variant'],
  },
  Textarea: {
    template: '<textarea :id="id" :disabled="disabled" :placeholder="placeholder" :rows="rows" class="resize-none"></textarea>',
    props: ['id', 'modelValue', 'disabled', 'placeholder', 'rows'],
  },
  Field: {
    template: '<div><slot /></div>',
  },
  FieldGroup: {
    template: '<div><slot /></div>',
  },
  FieldLabel: {
    template: '<label><slot /></label>',
  },
  RadioGroup: {
    template: '<div role="radiogroup" class="flex gap-4"><slot /></div>',
    props: ['modelValue'],
  },
  RadioGroupItem: {
    template: '<input type="radio" :value="value" :id="id" />',
    props: ['value', 'id'],
  },
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

describe('InterventionStep3', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    // Mock intervention query
    vi.mocked(interventionsApi.useInterventionQuery).mockReturnValue({
      data: ref(null),
      isLoading: ref(false),
      error: ref(null),
      status: ref('success'),
      state: ref({} as any),
      asyncStatus: ref('success'),
      refetch: vi.fn(),
      refresh: vi.fn(),
    } as any)

    // Mock mutation
    vi.mocked(interventionsApi.useUpdateInterventionMutation).mockReturnValue({
      mutate: vi.fn(),
      isPending: ref(false),
      error: ref(null),
    } as any)
  })

  describe('Rendering', () => {
    it('renders step 3 title', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Étape 3: Remarques')).toBeInTheDocument()
    })

    it('renders client remark radio group', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Remarque client')).toBeInTheDocument()
      // Radio options
      const nonLabels = screen.getAllByText('Non')
      const ouiLabels = screen.getAllByText('Oui')
      expect(nonLabels.length).toBeGreaterThan(0)
      expect(ouiLabels.length).toBeGreaterThan(0)
    })

    it('renders intervention end remark radio group', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Remarque fin d\'intervention')).toBeInTheDocument()
    })

    it('renders client request textarea', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Besoin client')).toBeInTheDocument()
      const textarea = screen.getByLabelText('Besoin client')
      expect(textarea).toBeInTheDocument()
      expect(textarea).toHaveAttribute('id', 'clientRequest')
    })

    it('renders final note textarea', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Fin intervention')).toBeInTheDocument()
    })

    it('renders breadcrumb navigation', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Type')).toBeInTheDocument()
      expect(screen.getByText('Remarque')).toBeInTheDocument()
      expect(screen.getByText('Acte')).toBeInTheDocument()
    })
  })

  describe('Navigation Buttons', () => {
    it('renders all navigation buttons', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Précédent')).toBeInTheDocument()
      expect(screen.getByText('Suivant')).toBeInTheDocument()
    })

    it('emits back event when back button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep3, {
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

  })

  describe('Form Fields', () => {
    it('has correct textarea attributes', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: false,
        },
        global: { stubs },
      })

      const clientTextarea = screen.getByLabelText('Besoin client')
      expect(clientTextarea).toHaveAttribute('rows', '6')

      const finalNoteTextarea = screen.getByLabelText('Fin intervention')
      expect(finalNoteTextarea).toHaveAttribute('rows', '6')
    })

  })

  describe('Edit Mode', () => {
    it('loads intervention data in edit mode', () => {
      vi.mocked(interventionsApi.useInterventionQuery).mockReturnValue({
        data: ref(mockIntervention),
        isLoading: ref(false),
        error: ref(null),
        status: ref('success'),
        state: ref({} as any),
        asyncStatus: ref('success'),
        refetch: vi.fn(),
        refresh: vi.fn(),
      } as any)

      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: true,
        },
        global: { stubs },
      })

      // Component should render with data loaded
      expect(screen.getByText('Étape 3: Remarques')).toBeInTheDocument()
    })
  })

  describe('Props', () => {
    it('accepts interventionId prop', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 123,
          isEditMode: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Étape 3: Remarques')).toBeInTheDocument()
    })

    it('accepts isEditMode prop', () => {
      render(InterventionStep3, {
        props: {
          interventionId: 1,
          isEditMode: true,
        },
        global: { stubs },
      })

      expect(screen.getByText('Étape 3: Remarques')).toBeInTheDocument()
    })
  })
})
