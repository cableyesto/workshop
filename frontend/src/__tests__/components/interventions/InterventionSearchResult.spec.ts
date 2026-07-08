import { describe, it, expect } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import '@testing-library/jest-dom'

import InterventionSearchResult from '@/components/interventions/InterventionSearchResult.vue'
import type { SearchInterventionResponse } from '@/types/intervention'

// Stubs for AlertDialog components
const stubs = {
  AlertDialog: {
    template: '<div v-if="open" data-testid="alert-dialog"><slot /></div>',
    props: ['open'],
  },
  AlertDialogContent: {
    template: '<div data-testid="alert-content"><slot /></div>',
  },
  AlertDialogHeader: {
    template: '<div data-testid="alert-header"><slot /></div>',
  },
  AlertDialogTitle: {
    template: '<h2 data-testid="alert-title"><slot /></h2>',
  },
  AlertDialogDescription: {
    template: '<p data-testid="alert-description"><slot /></p>',
  },
  AlertDialogFooter: {
    template: '<div data-testid="alert-footer"><slot /></div>',
  },
  AlertDialogCancel: {
    template: '<button data-testid="alert-cancel" @click="$emit(\'click\')"><slot /></button>',
  },
  AlertDialogAction: {
    template: '<button data-testid="alert-action" @click="$emit(\'click\')"><slot /></button>',
  },
}

describe('InterventionSearchResult', () => {
  describe('Rendering', () => {
    it('renders dialog when open is true', () => {
      const result: SearchInterventionResponse = {
        found: false,
        intervention: undefined,
      }

      render(InterventionSearchResult, {
        props: {
          open: true,
          result,
        },
        global: { stubs },
      })

      expect(screen.getByTestId('alert-dialog')).toBeInTheDocument()
    })

    it('does not render dialog when open is false', () => {
      const result: SearchInterventionResponse = {
        found: false,
        intervention: undefined,
      }

      render(InterventionSearchResult, {
        props: {
          open: false,
          result,
        },
        global: { stubs },
      })

      expect(screen.queryByTestId('alert-dialog')).not.toBeInTheDocument()
    })

    it('renders content when result is provided', () => {
      const result: SearchInterventionResponse = {
        found: false,
        intervention: undefined,
      }

      render(InterventionSearchResult, {
        props: {
          open: true,
          result,
        },
        global: { stubs },
      })

      expect(screen.getByTestId('alert-content')).toBeInTheDocument()
    })

    it('does not render content when result is null', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: null,
        },
        global: { stubs },
      })

      expect(screen.queryByTestId('alert-content')).not.toBeInTheDocument()
    })
  })

  describe('Intervention Found', () => {
    const foundResult: SearchInterventionResponse = {
      found: true,
      intervention: {
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
        status: 'In Progress',
        interventionType: 'Repair',
        documentType: 'Estimate',
        clientRemark: false,
        clientRequest: null,
        interventionEndRemark: false,
        finalNote: null,
      },
    }

    it('displays warning title when intervention exists', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: foundResult,
        },
        global: { stubs },
      })

      expect(screen.getByText('⚠️ Intervention existante')).toBeInTheDocument()
    })

    it('displays warning description', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: foundResult,
        },
        global: { stubs },
      })

      expect(
        screen.getByText(
          'Une intervention en cours existe déjà pour ce mécanicien et cette voiture.',
        ),
      ).toBeInTheDocument()
    })

    it('displays intervention status', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: foundResult,
        },
        global: { stubs },
      })

      expect(screen.getByText('Statut:')).toBeInTheDocument()
      expect(screen.getByText('In Progress')).toBeInTheDocument()
    })

    it('displays intervention date and time', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: foundResult,
        },
        global: { stubs },
      })

      expect(screen.getByText('Créée le:')).toBeInTheDocument()
      expect(screen.getByText('2024-01-15')).toBeInTheDocument()
      expect(screen.getByText(/à 10:30/)).toBeInTheDocument()
    })

    it('displays date without time when startTime is missing', () => {
      const resultNoTime: SearchInterventionResponse = {
        found: true,
        intervention: {
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
          startTime: '',
          status: 'In Progress',
          interventionType: 'Repair',
          documentType: 'Estimate',
          clientRemark: false,
          clientRequest: null,
          interventionEndRemark: false,
          finalNote: null,
        },
      }

      render(InterventionSearchResult, {
        props: {
          open: true,
          result: resultNoTime,
        },
        global: { stubs },
      })

      expect(screen.getByText('2024-01-15')).toBeInTheDocument()
      // Check that time text (à HH:MM) is not present
      expect(screen.queryByText(/à \d{2}:\d{2}/)).not.toBeInTheDocument()
    })

    it('shows cancel and edit buttons', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: foundResult,
        },
        global: { stubs },
      })

      expect(screen.getByText('Annuler')).toBeInTheDocument()
      expect(screen.getByText('Modifier l\'intervention')).toBeInTheDocument()
    })

    it('emits edit with intervention ID when edit clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionSearchResult, {
        props: {
          open: true,
          result: foundResult,
        },
        global: { stubs },
      })

      const editButton = screen.getByText('Modifier l\'intervention')
      await user.click(editButton)

      expect(emitted().edit).toBeTruthy()
      expect(emitted().edit![0]).toEqual([1])
    })

    it('emits cancel when cancel clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionSearchResult, {
        props: {
          open: true,
          result: foundResult,
        },
        global: { stubs },
      })

      const cancelButton = screen.getByText('Annuler')
      await user.click(cancelButton)

      expect(emitted().cancel).toBeTruthy()
    })
  })

  describe('Intervention Not Found', () => {
    const notFoundResult: SearchInterventionResponse = {
      found: false,
      intervention: undefined,
    }

    it('displays success title when no intervention exists', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: notFoundResult,
        },
        global: { stubs },
      })

      expect(screen.getByText('✓ Aucune intervention en cours')).toBeInTheDocument()
    })

    it('displays success description', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: notFoundResult,
        },
        global: { stubs },
      })

      expect(
        screen.getByText('Aucune intervention en cours trouvée pour cette combinaison.'),
      ).toBeInTheDocument()
    })

    it('shows cancel and create buttons', () => {
      render(InterventionSearchResult, {
        props: {
          open: true,
          result: notFoundResult,
        },
        global: { stubs },
      })

      expect(screen.getByText('Annuler')).toBeInTheDocument()
      expect(screen.getByText('Créer le processus')).toBeInTheDocument()
    })

    it('emits create when create button clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionSearchResult, {
        props: {
          open: true,
          result: notFoundResult,
        },
        global: { stubs },
      })

      const createButton = screen.getByText('Créer le processus')
      await user.click(createButton)

      expect(emitted().create).toBeTruthy()
    })

    it('emits cancel when cancel clicked', async () => {
      const user = userEvent.setup()
      const { emitted } = render(InterventionSearchResult, {
        props: {
          open: true,
          result: notFoundResult,
        },
        global: { stubs },
      })

      const cancelButton = screen.getByText('Annuler')
      await user.click(cancelButton)

      expect(emitted().cancel).toBeTruthy()
    })
  })
})
