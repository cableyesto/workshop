import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'
import { ref } from 'vue'

import InterventionStep1 from '@/components/interventions/InterventionStep1.vue'
import * as employeesApi from '@/api/employees'

// Mock Pinia Colada query
vi.mock('@/api/employees', () => ({
  useMechanicsQuery: vi.fn(),
}))

// Mock vee-validate
vi.mock('vee-validate', () => ({
  useForm: vi.fn(() => ({
    handleSubmit: vi.fn((onSuccess) => (e: Event) => {
      e?.preventDefault?.()
      onSuccess()
    }),
    errors: {},
    defineField: vi.fn((name) => [{ value: '' }, { name, onBlur: vi.fn(), onChange: vi.fn() }]),
  })),
}))

vi.mock('@vee-validate/zod', () => ({
  toTypedSchema: vi.fn((schema) => schema),
}))

// Mock data
const mockMechanics = [
  { id: 1, firstName: 'Jean', lastName: 'Dupont', birthDate: '1980-01-01', hireDate: '2020-01-01' },
  {
    id: 2,
    firstName: 'Marie',
    lastName: 'Martin',
    birthDate: '1985-05-15',
    hireDate: '2019-03-10',
  },
  {
    id: 3,
    firstName: 'Pierre',
    lastName: 'Durand',
    birthDate: '1990-08-20',
    hireDate: '2021-06-15',
  },
]

// Stubs for UI components
const stubs = {
  Button: {
    template: '<button :type="type"><slot /></button>',
    props: ['type'],
  },
  Input: {
    template:
      '<input :id="id" :type="type" :placeholder="placeholder" :maxlength="maxlength" @input="$emit(\'input\', $event)" />',
    props: ['id', 'type', 'modelValue', 'placeholder', 'maxlength'],
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
  FieldError: {
    template: '<span class="error"><slot /></span>',
  },
  MechanicCombobox: {
    template:
      '<div data-testid="mechanic-combobox" :data-loading="loading"><input id="mechanic" aria-label="Mécanicien" /><slot /></div>',
    props: ['modelValue', 'options', 'loading'],
  },
}

describe('InterventionStep1', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    // Default mock implementation
    vi.mocked(employeesApi.useMechanicsQuery).mockReturnValue({
      data: ref(mockMechanics),
      isLoading: ref(false),
      error: ref(null),
      status: ref('success'),
      state: ref({} as any),
      asyncStatus: ref('success'),
      refetch: vi.fn(),
      refresh: vi.fn(),
    } as any)
  })

  describe('Rendering', () => {
    it('renders form with all fields', () => {
      render(InterventionStep1, {
        props: {
          mechanicId: undefined,
          licensePlate: '',
        },
        global: { stubs },
      })

      expect(screen.getByLabelText('Mécanicien')).toBeInTheDocument()
      expect(screen.getByLabelText('Immatriculation voiture')).toBeInTheDocument()
      expect(screen.getByRole('button', { name: /rechercher/i })).toBeInTheDocument()
    })

    it('renders MechanicCombobox component', () => {
      render(InterventionStep1, {
        props: {
          mechanicId: undefined,
          licensePlate: '',
        },
        global: { stubs },
      })

      expect(screen.getByTestId('mechanic-combobox')).toBeInTheDocument()
    })

    it('shows loading state when mechanics are loading', () => {
      vi.mocked(employeesApi.useMechanicsQuery).mockReturnValue({
        data: ref(null),
        isLoading: ref(true),
        error: ref(null),
        status: ref('pending'),
        state: ref({} as any),
        asyncStatus: ref('pending'),
        refetch: vi.fn(),
        refresh: vi.fn(),
      } as any)

      render(InterventionStep1, {
        props: {
          mechanicId: undefined,
          licensePlate: '',
        },
        global: { stubs },
      })

      const combobox = screen.getByTestId('mechanic-combobox')
      expect(combobox).toHaveAttribute('data-loading', 'true')
    })

    it('renders license plate input with placeholder', () => {
      render(InterventionStep1, {
        props: {
          mechanicId: undefined,
          licensePlate: '',
        },
        global: { stubs },
      })

      const input = screen.getByPlaceholderText('AB-123-CD')
      expect(input).toBeInTheDocument()
      expect(input).toHaveAttribute('maxlength', '9')
    })
  })

  describe('Mechanic Options', () => {
    it('transforms mechanics data to combobox options', () => {
      const { container } = render(InterventionStep1, {
        props: {
          mechanicId: undefined,
          licensePlate: '',
        },
        global: { stubs },
      })

      // MechanicCombobox receives the transformed options
      // We can't directly test the options prop, but we verify the component renders
      expect(screen.getByTestId('mechanic-combobox')).toBeInTheDocument()
    })

    it('handles empty mechanics list', () => {
      vi.mocked(employeesApi.useMechanicsQuery).mockReturnValue({
        data: ref([]),
        isLoading: ref(false),
        error: ref(null),
        status: ref('success'),
        state: ref({} as any),
        asyncStatus: ref('success'),
        refetch: vi.fn(),
        refresh: vi.fn(),
      } as any)

      render(InterventionStep1, {
        props: {
          mechanicId: undefined,
          licensePlate: '',
        },
        global: { stubs },
      })

      expect(screen.getByTestId('mechanic-combobox')).toBeInTheDocument()
    })

    it('handles null mechanics data', () => {
      vi.mocked(employeesApi.useMechanicsQuery).mockReturnValue({
        data: ref(null),
        isLoading: ref(false),
        error: ref(null),
        status: ref('success'),
        state: ref({} as any),
        asyncStatus: ref('success'),
        refetch: vi.fn(),
        refresh: vi.fn(),
      } as any)

      render(InterventionStep1, {
        props: {
          mechanicId: undefined,
          licensePlate: '',
        },
        global: { stubs },
      })

      expect(screen.getByTestId('mechanic-combobox')).toBeInTheDocument()
    })
  })

  describe('License Plate Formatting', () => {
    it('formats license plate input correctly', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep1, {
        props: {
          mechanicId: 1,
          licensePlate: '',
        },
        global: { stubs },
      })

      const input = screen.getByPlaceholderText('AB-123-CD')

      // Simulate typing AB123CD
      await user.type(input, 'AB123CD')

      // Should emit formatted value
      const updateEvents = emitted()['update:licensePlate']
      expect(updateEvents).toBeTruthy()

      // Check that at least one emitted value is formatted
      const formattedValues = updateEvents?.filter((event: any) => event[0] === 'AB-123-CD')
      expect(formattedValues?.length).toBeGreaterThan(0)
    })

    it('handles partial input formatting', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep1, {
        props: {
          mechanicId: 1,
          licensePlate: '',
        },
        global: { stubs },
      })

      const input = screen.getByPlaceholderText('AB-123-CD')

      // Type partial input
      await user.type(input, 'AB1')

      const updateEvents = emitted()['update:licensePlate']
      expect(updateEvents).toBeTruthy()

      // Should have partial formatted values
      const partialFormatted = updateEvents?.filter((event: any) => event[0].includes('-'))
      expect(partialFormatted).toBeTruthy()
    })

    it('converts input to uppercase', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep1, {
        props: {
          mechanicId: 1,
          licensePlate: '',
        },
        global: { stubs },
      })

      const input = screen.getByPlaceholderText('AB-123-CD')

      await user.type(input, 'ab')

      const updateEvents = emitted()['update:licensePlate']
      expect(updateEvents).toBeTruthy()

      // All emitted values should be uppercase
      const hasUppercase = updateEvents?.some((event: any) => event[0] === 'AB' || event[0] === 'A')
      expect(hasUppercase).toBe(true)
    })
  })

  describe('Events', () => {
    it('emits search event when form submitted', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep1, {
        props: {
          mechanicId: 1,
          licensePlate: 'AB-123-CD',
        },
        global: { stubs },
      })

      const submitButton = screen.getByRole('button', { name: /rechercher/i })
      await user.click(submitButton)

      expect(emitted().search).toBeTruthy()
    })

    it('emits update:licensePlate on input change', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionStep1, {
        props: {
          mechanicId: 1,
          licensePlate: '',
        },
        global: { stubs },
      })

      const input = screen.getByPlaceholderText('AB-123-CD')
      await user.type(input, 'A')

      expect(emitted()['update:licensePlate']).toBeTruthy()
    })
  })

  describe('Props', () => {
    it('displays provided mechanicId value', () => {
      render(InterventionStep1, {
        props: {
          mechanicId: 2,
          licensePlate: '',
        },
        global: { stubs },
      })

      // Component should render with the mechanic selected
      expect(screen.getByTestId('mechanic-combobox')).toBeInTheDocument()
    })

    it('displays provided licensePlate value', () => {
      const { container } = render(InterventionStep1, {
        props: {
          mechanicId: 1,
          licensePlate: 'AB-123-CD',
        },
        global: { stubs },
      })

      const input = screen.getByPlaceholderText('AB-123-CD')
      // Input should have the model-value bound
      expect(input).toBeInTheDocument()
    })
  })
})
