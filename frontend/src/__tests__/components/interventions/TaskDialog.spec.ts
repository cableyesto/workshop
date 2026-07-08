import { describe, it, expect, vi } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'

import TaskDialog from '@/components/interventions/TaskDialog.vue'
import type { Task } from '@/types/task'

// Mock vee-validate
vi.mock('vee-validate', () => ({
  useForm: vi.fn(() => ({
    handleSubmit: vi.fn((onSuccess) => (e: Event) => {
      e?.preventDefault?.()
      onSuccess({
        name: 'Test Task',
        quantity: 2,
        unitPrice: 50.0,
      })
    }),
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
const mockTask: Task = {
  id: 1,
  name: 'Vidange moteur',
  quantity: 1,
  unitPrice: 45.5,
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
    template: '<button :type="type"><slot /></button>',
    props: ['type', 'variant'],
  },
  Input: {
    template: '<input :id="id" :type="type" :min="min" :step="step" />',
    props: ['id', 'type', 'modelValue', 'min', 'step'],
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

describe('TaskDialog', () => {
  describe('Rendering', () => {
    it('renders dialog when open is true', () => {
      render(TaskDialog, {
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
      render(TaskDialog, {
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
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByText('Ajouter un acte')).toBeInTheDocument()
      expect(screen.getByText('Remplissez les informations du nouvel acte.')).toBeInTheDocument()
    })

    it('shows all form fields', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByLabelText("Nom de l'acte")).toBeInTheDocument()
      expect(screen.getByLabelText('Quantité')).toBeInTheDocument()
      expect(screen.getByLabelText(/Prix unitaire/)).toBeInTheDocument()
    })

    it('shows correct button labels', () => {
      render(TaskDialog, {
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
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'edit',
          task: mockTask,
        },
        global: { stubs },
      })

      expect(screen.getByText('Modifier un acte')).toBeInTheDocument()
      expect(screen.getByText("Modifiez les informations de l'acte.")).toBeInTheDocument()
    })

    it('shows correct button labels', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'edit',
          task: mockTask,
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
      const { emitted } = render(TaskDialog, {
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

    it('emits submit when form submitted', async () => {
      const user = userEvent.setup()
      const { emitted } = render(TaskDialog, {
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
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      const nameInput = screen.getByLabelText("Nom de l'acte")
      expect(nameInput).toHaveAttribute('type', 'text')

      const quantityInput = screen.getByLabelText('Quantité')
      expect(quantityInput).toHaveAttribute('type', 'number')
      expect(quantityInput).toHaveAttribute('min', '1')
      expect(quantityInput).toHaveAttribute('step', '1')

      const priceInput = screen.getByLabelText(/Prix unitaire/)
      expect(priceInput).toHaveAttribute('type', 'number')
      expect(priceInput).toHaveAttribute('min', '0')
      expect(priceInput).toHaveAttribute('step', '0.5')
    })
  })

  describe('Computed Properties', () => {
    it('computes dialog title for create mode', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByText('Ajouter un acte')).toBeInTheDocument()
    })

    it('computes dialog title for edit mode', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'edit',
          task: mockTask,
        },
        global: { stubs },
      })

      expect(screen.getByText('Modifier un acte')).toBeInTheDocument()
    })

    it('computes dialog description for create mode', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByText('Remplissez les informations du nouvel acte.')).toBeInTheDocument()
    })

    it('computes dialog description for edit mode', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'edit',
          task: mockTask,
        },
        global: { stubs },
      })

      expect(screen.getByText("Modifiez les informations de l'acte.")).toBeInTheDocument()
    })
  })

  describe('Form Attributes', () => {
    it('has correct button types', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: {
          stubs: {
            ...stubs,
            Button: {
              template: '<button :type="type"><slot /></button>',
              props: ['type', 'variant'],
            },
          },
        },
      })

      const cancelButton = screen.getByText('Annuler')
      const submitButton = screen.getByText('Ajouter')

      expect(cancelButton).toHaveAttribute('type', 'button')
      expect(submitButton).toHaveAttribute('type', 'submit')
    })

    it('has correct field IDs matching labels', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      const nameInput = screen.getByLabelText("Nom de l'acte")
      expect(nameInput).toHaveAttribute('id', 'name')

      const quantityInput = screen.getByLabelText('Quantité')
      expect(quantityInput).toHaveAttribute('id', 'quantity')

      const priceInput = screen.getByLabelText(/Prix unitaire/)
      expect(priceInput).toHaveAttribute('id', 'unitPrice')
    })

    it('does not show error messages initially', () => {
      render(TaskDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      // FieldError components should not be visible initially
      const errors = screen.queryAllByText(/requis|négatif|entier|dépasser/i)
      expect(errors).toHaveLength(0)
    })
  })
})
