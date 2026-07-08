import { describe, it, expect } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'

import MechanicCombobox from '@/components/interventions/MechanicCombobox.vue'

// Mock lucide-vue-next icons
const MockIcon = {
  name: 'MockIcon',
  template: '<svg data-testid="mock-icon" />',
}

// Stubs for UI components
const stubs = {
  Popover: {
    template: '<div data-testid="popover"><slot /></div>',
    props: ['open'],
  },
  PopoverTrigger: {
    template: '<div data-testid="popover-trigger"><slot /></div>',
  },
  PopoverContent: {
    template: '<div data-testid="popover-content"><slot /></div>',
  },
  Button: {
    template: '<button :disabled="disabled" :role="role"><slot /></button>',
    props: ['variant', 'role', 'disabled'],
  },
  Command: {
    template: '<div data-testid="command"><slot /></div>',
  },
  CommandInput: {
    template: '<input data-testid="command-input" :placeholder="placeholder" />',
    props: ['placeholder'],
  },
  CommandList: {
    template: '<div data-testid="command-list"><slot /></div>',
  },
  CommandEmpty: {
    template: '<div data-testid="command-empty"><slot /></div>',
  },
  CommandGroup: {
    template: '<div data-testid="command-group"><slot /></div>',
  },
  CommandItem: {
    template: '<div data-testid="command-item" @click="$emit(\'select\')"><slot /></div>',
    props: ['value'],
  },
  Check: MockIcon,
  ChevronsUpDown: MockIcon,
}

// Test data
const mockOptions = [
  { value: 1, label: 'Jean Dupont' },
  { value: 2, label: 'Marie Martin' },
  { value: 3, label: 'Pierre Durand' },
]

describe('MechanicCombobox', () => {
  describe('Rendering', () => {
    it('renders with placeholder when no value selected', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
        },
        global: { stubs },
      })

      expect(screen.getByText('Sélectionnez un mécanicien')).toBeInTheDocument()
    })

    it('renders with custom placeholder', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
          placeholder: 'Choisir un mécano',
        },
        global: { stubs },
      })

      expect(screen.getByText('Choisir un mécano')).toBeInTheDocument()
    })

    it('displays selected mechanic label', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: 2,
          options: mockOptions,
        },
        global: { stubs },
      })

      const button = screen.getByRole('combobox')
      expect(button).toHaveTextContent('Marie Martin')
    })

    it('renders all mechanic options', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
        },
        global: { stubs },
      })

      expect(screen.getByText('Jean Dupont')).toBeInTheDocument()
      expect(screen.getByText('Marie Martin')).toBeInTheDocument()
      expect(screen.getByText('Pierre Durand')).toBeInTheDocument()
    })
  })

  describe('States', () => {
    it('shows loading message when loading', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: [],
          loading: true,
        },
        global: { stubs },
      })

      expect(screen.getByText('Chargement...')).toBeInTheDocument()
    })

    it('shows empty message when no options and not loading', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: [],
          loading: false,
        },
        global: { stubs },
      })

      expect(screen.getByText('Aucun mécanicien trouvé.')).toBeInTheDocument()
    })

    it('disables button when disabled prop is true', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
          disabled: true,
        },
        global: { stubs },
      })

      const button = screen.getByRole('combobox')
      expect(button).toBeDisabled()
    })

    it('enables button when disabled prop is false', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
          disabled: false,
        },
        global: { stubs },
      })

      const button = screen.getByRole('combobox')
      expect(button).not.toBeDisabled()
    })
  })

  describe('Interactions', () => {
    it('emits update:modelValue when option selected', async () => {
      const user = userEvent.setup()
      const { emitted } = render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
        },
        global: { stubs },
      })

      // Find all command items
      const items = screen.getAllByTestId('command-item')

      // Click first option (Jean Dupont)
      await user.click(items[0]!)

      expect(emitted()['update:modelValue']).toBeTruthy()
      expect(emitted()['update:modelValue']![0]).toEqual([1])
    })

    it('emits correct value when different option selected', async () => {
      const user = userEvent.setup()
      const { emitted } = render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
        },
        global: { stubs },
      })

      const items = screen.getAllByTestId('command-item')

      // Click third option (Pierre Durand)
      await user.click(items[2]!)

      expect(emitted()['update:modelValue']![0]).toEqual([3])
    })
  })

  describe('Computed Properties', () => {
    it('computes selectedLabel correctly when value is selected', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: 1,
          options: mockOptions,
        },
        global: { stubs },
      })

      // Should display the label for value 1 in the button
      const button = screen.getByRole('combobox')
      expect(button).toHaveTextContent('Jean Dupont')
    })

    it('computes selectedLabel as placeholder when no value', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
        },
        global: { stubs },
      })

      const button = screen.getByRole('combobox')
      expect(button).toHaveTextContent('Sélectionnez un mécanicien')
    })

    it('computes selectedLabel as placeholder when value not found in options', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: 999, // Non-existent value
          options: mockOptions,
        },
        global: { stubs },
      })

      const button = screen.getByRole('combobox')
      expect(button).toHaveTextContent('Sélectionnez un mécanicien')
    })
  })

  describe('UI Elements', () => {
    it('has combobox role on trigger button', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
        },
        global: { stubs },
      })

      const button = screen.getByRole('combobox')
      expect(button).toBeInTheDocument()
    })

    it('has search input with correct placeholder', () => {
      render(MechanicCombobox, {
        props: {
          modelValue: undefined,
          options: mockOptions,
        },
        global: { stubs },
      })

      const searchInput = screen.getByPlaceholderText('Rechercher un mécanicien...')
      expect(searchInput).toBeInTheDocument()
    })
  })
})
