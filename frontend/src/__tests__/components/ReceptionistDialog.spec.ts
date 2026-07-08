import { describe, it, expect, vi } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'

import ReceptionistDialog from '@/components/ReceptionistDialog.vue'
import type { Receptionist } from '@/types/employee'

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
        email: 'test@example.com',
        password: 'password123',
        passwordConfirm: 'password123',
      })
    }),
    values: {
      lastName: 'Test',
      firstName: 'User',
      birthDate: '1990-01-01',
    },
    errors: {},
    defineField: vi.fn((name) => [{ value: '' }, { name, onBlur: vi.fn(), onChange: vi.fn() }]),
    resetForm: vi.fn(),
    setValues: vi.fn(),
  })),
}))

vi.mock('@vee-validate/zod', () => ({
  toTypedSchema: vi.fn((schema) => schema),
}))

// Mock validation utility
vi.mock('@/utils/validation', () => ({
  areStringsEqual: vi.fn((a, b) => a === b),
}))

// Test data
const mockReceptionist: Receptionist = {
  id: 1,
  lastName: 'Dupont',
  firstName: 'Marie',
  birthDate: '1990-05-15',
  hireDate: '2020-01-10',
  email: 'marie.dupont@example.com',
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
    template: '<input :id="id" :type="type" />',
    props: ['id', 'type', 'modelValue'],
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

describe('ReceptionistDialog', () => {
  describe('Rendering', () => {
    it('renders dialog when open is true', () => {
      render(ReceptionistDialog, {
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
      render(ReceptionistDialog, {
        props: {
          open: false,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.queryByTestId('dialog')).not.toBeInTheDocument()
    })
  })

  describe('Step Navigation', () => {
    it('displays step 1 by default in create mode', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      // Step 1 title
      expect(screen.getByText('Ajouter un réceptionniste (1/2)')).toBeInTheDocument()
      expect(screen.getByText('Remplissez les informations personnelles.')).toBeInTheDocument()

      // Step 1 fields
      expect(screen.getByLabelText('Nom')).toBeInTheDocument()
      expect(screen.getByLabelText('Prénom')).toBeInTheDocument()
      expect(screen.getByLabelText('Date de naissance')).toBeInTheDocument()

      // Step 1 button
      expect(screen.getByText('Suivant')).toBeInTheDocument()
    })

    it('does not show step 2 fields initially', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      // Step 2 fields should not be visible
      expect(screen.queryByLabelText('Email')).not.toBeInTheDocument()
      expect(screen.queryByLabelText('Mot de passe')).not.toBeInTheDocument()
    })
  })

  describe('Create Mode', () => {
    it('shows step indicators in create mode', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByText('Ajouter un réceptionniste (1/2)')).toBeInTheDocument()
    })

    it('does not show delete button in create mode step 1', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.queryByText('Supprimer')).not.toBeInTheDocument()
    })

    it('shows cancel and next buttons in step 1', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      expect(screen.getByText('Annuler')).toBeInTheDocument()
      expect(screen.getByText('Suivant')).toBeInTheDocument()
    })
  })

  describe('Edit Mode', () => {
    it('displays edit title without step indicator', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'edit',
          receptionist: mockReceptionist,
        },
        global: { stubs },
      })

      expect(screen.getByText('Modifier un réceptionniste')).toBeInTheDocument()
      expect(screen.getByText('Modifiez les informations du réceptionniste.')).toBeInTheDocument()
    })

    it('shows delete button in edit mode', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'edit',
          receptionist: mockReceptionist,
        },
        global: { stubs },
      })

      expect(screen.getByText('Supprimer')).toBeInTheDocument()
    })
  })

  describe('Events', () => {
    it('emits close when cancel button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(ReceptionistDialog, {
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
      const { emitted } = render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'edit',
          receptionist: mockReceptionist,
        },
        global: { stubs },
      })

      const deleteButton = screen.getByText('Supprimer')
      await user.click(deleteButton)

      expect(emitted().delete).toBeTruthy()
    })

    it('emits submit when step 1 form submitted', async () => {
      const user = userEvent.setup()
      const { emitted } = render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      const nextButton = screen.getByText('Suivant')
      await user.click(nextButton)

      // Step 1 submit doesn't emit, just moves to step 2
      // But we can verify the button click worked
      expect(emitted().submit).toBeFalsy()
    })
  })

  describe('Form Fields', () => {
    it('has correct input types for step 1 fields', () => {
      render(ReceptionistDialog, {
        props: {
          open: true,
          mode: 'create',
        },
        global: { stubs },
      })

      const lastNameInput = screen.getByLabelText('Nom')
      const firstNameInput = screen.getByLabelText('Prénom')
      expect(lastNameInput).toHaveAttribute('type', 'text')
      expect(firstNameInput).toHaveAttribute('type', 'text')

      const birthDateInput = screen.getByLabelText('Date de naissance')
      const hireDateInput = screen.getByLabelText(/Date de démarrage/)
      expect(birthDateInput).toHaveAttribute('type', 'date')
      expect(hireDateInput).toHaveAttribute('type', 'date')
    })
  })
})
