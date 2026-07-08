import { describe, it, expect, vi } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'

import MechanicDialog from '@/components/MechanicDialog.vue'
import type { Mechanic } from '@/types/employee'

// Mock vee-validate
vi.mock('vee-validate', () => ({
  useForm: vi.fn(() => ({
    handleSubmit: vi.fn((onSuccess) => (e: Event) => {
      e?.preventDefault?.()
      onSuccess({
        lastName: 'Test',
        firstName: 'User',
        birthDate: '1990-01-01',
        hireDate: '2020-01-01',
        pin: '1234',
      })
    }),
    values: {},
    errors: {},
    defineField: vi.fn((name) => [{ value: '' }, { name, onBlur: vi.fn(), onChange: vi.fn() }]),
    resetForm: vi.fn(),
    setValues: vi.fn(),
  })),
}))

vi.mock('@vee-validate/zod', () => ({
  toTypedSchema: vi.fn((schema) => schema),
}))

// Test data
const mockMechanic: Mechanic = {
  id: 1,
  lastName: 'Dupont',
  firstName: 'Jean',
  birthDate: '1990-05-15',
  hireDate: '2020-01-10',
}

// Stubs for UI components
const stubs = {
  Dialog: {
    template: '<div v-if="open" data-testid="dialog"><slot /></div>',
    props: ['open'],
  },
  DialogContent: {
    template: '<div data-testid="dialog-content"><slot /></div>',
  },
  DialogHeader: {
    template: '<div data-testid="dialog-header"><slot /></div>',
  },
  DialogTitle: {
    template: '<h2 data-testid="dialog-title"><slot /></h2>',
  },
  DialogDescription: {
    template: '<p data-testid="dialog-description"><slot /></p>',
  },
  DialogFooter: {
    template: '<div data-testid="dialog-footer"><slot /></div>',
  },
  Button: {
    template: '<button :type="type" data-testid="button"><slot /></button>',
    props: ['type', 'variant'],
  },
  Input: {
    template:
      '<input :id="id" :type="type" :maxlength="maxlength" :placeholder="placeholder" data-testid="input" />',
    props: ['id', 'type', 'modelValue', 'maxlength', 'inputmode', 'placeholder'],
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
}

describe('MechanicDialog', () => {
  describe('Rendering', () => {
    it('renders dialog when open is true', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByTestId('dialog')).toBeInTheDocument()
      expect(screen.getByTestId('dialog-content')).toBeInTheDocument()
    })

    it('does not render dialog when open is false', () => {
      render(MechanicDialog, {
        props: {
          open: false,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.queryByTestId('dialog')).not.toBeInTheDocument()
    })
  })

  describe('Create Mode', () => {
    it('displays create title and description', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByText('Ajouter un mécanicien')).toBeInTheDocument()
      expect(
        screen.getByText('Remplissez les informations du nouveau mécanicien.'),
      ).toBeInTheDocument()
    })

    it('shows all form fields', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByLabelText('Nom')).toBeInTheDocument()
      expect(screen.getByLabelText('Prénom')).toBeInTheDocument()
      expect(screen.getByLabelText('Date de naissance')).toBeInTheDocument()
      expect(screen.getByLabelText(/Date de démarrage/)).toBeInTheDocument()
      expect(screen.getByLabelText(/Code PIN/)).toBeInTheDocument()
    })

    it('does not show delete button in create mode', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.queryByText('Supprimer')).not.toBeInTheDocument()
    })

    it('shows correct button labels', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByText('Ajouter')).toBeInTheDocument()
      expect(screen.getByText('Annuler')).toBeInTheDocument()
    })
  })

  describe('Edit Mode', () => {
    it('displays edit title and description', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'edit',
          mechanic: mockMechanic,
        },
        global: { stubs },
      })

      expect(screen.getByText('Modifier un mécanicien')).toBeInTheDocument()
      expect(screen.getByText('Modifiez les informations du mécanicien.')).toBeInTheDocument()
    })

    it('shows delete button in edit mode', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'edit',
          mechanic: mockMechanic,
        },
        global: { stubs },
      })

      expect(screen.getByText('Supprimer')).toBeInTheDocument()
    })

    it('shows correct button labels', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'edit',
          mechanic: mockMechanic,
        },
        global: { stubs },
      })

      expect(screen.getByText('Modifier')).toBeInTheDocument()
      expect(screen.getByText('Annuler')).toBeInTheDocument()
    })
  })

  describe('Events', () => {
    it('emits close when cancel button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      const cancelButton = screen.getByText('Annuler')
      await user.click(cancelButton)

      expect(emitted().close).toBeTruthy()
    })

    it('emits delete when delete button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(MechanicDialog, {
        props: {
          open: true,
          mode: 'edit',
          mechanic: mockMechanic,
        },
        global: { stubs },
      })

      const deleteButton = screen.getByText('Supprimer')
      await user.click(deleteButton)

      expect(emitted().delete).toBeTruthy()
    })

    it('emits submit when form submitted', async () => {
      const user = userEvent.setup()
      const { emitted } = render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      const submitButton = screen.getByText('Ajouter')
      await user.click(submitButton)

      expect(emitted().submit).toBeTruthy()
      expect(emitted().submit![0]).toBeTruthy()
    })
  })

  describe('Form Fields', () => {
    it('has correct input types for each field', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      // Check lastName and firstName are text inputs
      const lastNameInput = screen.getByLabelText('Nom')
      const firstNameInput = screen.getByLabelText('Prénom')
      expect(lastNameInput).toHaveAttribute('type', 'text')
      expect(firstNameInput).toHaveAttribute('type', 'text')

      // Check dates are date inputs
      const birthDateInput = screen.getByLabelText('Date de naissance')
      const hireDateInput = screen.getByLabelText(/Date de démarrage/)
      expect(birthDateInput).toHaveAttribute('type', 'date')
      expect(hireDateInput).toHaveAttribute('type', 'date')

      // Check PIN is text with maxlength
      const pinInput = screen.getByLabelText(/Code PIN/)
      expect(pinInput).toHaveAttribute('type', 'text')
      expect(pinInput).toHaveAttribute('maxlength', '4')
    })

    it('displays placeholder for PIN field', () => {
      render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      const pinInput = screen.getByLabelText(/Code PIN/)
      expect(pinInput).toHaveAttribute('placeholder', '0000')
    })
  })

  describe('Button Variants', () => {
    it('delete button has destructive variant in edit mode', () => {
      const { container } = render(MechanicDialog, {
        props: {
          open: true,
          mode: 'edit',
          mechanic: mockMechanic,
        },
        global: {
          stubs: {
            ...stubs,
            Button: {
              template: '<button :type="type" :data-variant="variant"><slot /></button>',
              props: ['type', 'variant'],
            },
          },
        },
      })

      const deleteButton = screen.getByText('Supprimer')
      expect(deleteButton).toHaveAttribute('data-variant', 'destructive')
    })

    it('cancel button has outline variant', () => {
      const { container } = render(MechanicDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: {
          stubs: {
            ...stubs,
            Button: {
              template: '<button :type="type" :data-variant="variant"><slot /></button>',
              props: ['type', 'variant'],
            },
          },
        },
      })

      const cancelButton = screen.getByText('Annuler')
      expect(cancelButton).toHaveAttribute('data-variant', 'outline')
    })
  })
})
