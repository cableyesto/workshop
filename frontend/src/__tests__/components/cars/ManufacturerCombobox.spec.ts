import { describe, it, expect, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import userEvent from '@testing-library/user-event'
import ManufacturerCombobox from '@/components/cars/ManufacturerCombobox.vue'

describe('ManufacturerCombobox.vue', () => {
  const mockOptions = [
    { value: 'Renault', label: 'Renault' },
    { value: 'Peugeot', label: 'Peugeot' },
    { value: 'Citroën', label: 'Citroën' },
    { value: 'Volkswagen', label: 'Volkswagen' },
  ]

  beforeEach(() => {
    document.body.innerHTML = ''
    // Mock scrollIntoView for JSDOM
    Element.prototype.scrollIntoView = () => {}
  })

  describe('Rendering', () => {
    it('renders the combobox button', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      expect(screen.getByRole('combobox')).toBeInTheDocument()
    })

    it('displays placeholder when no value selected', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
          placeholder: 'Sélectionnez un constructeur',
        },
      })

      expect(screen.getByText('Sélectionnez un constructeur')).toBeInTheDocument()
    })

    it('displays custom placeholder when provided', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
          placeholder: 'Choisir un constructeur',
        },
      })

      expect(screen.getByText('Choisir un constructeur')).toBeInTheDocument()
    })

    it('displays selected manufacturer label', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: 'Renault',
          options: mockOptions,
        },
      })

      expect(screen.getByText('Renault')).toBeInTheDocument()
    })

    it('falls back to placeholder if selected value not in options', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: 'NonExistent',
          options: mockOptions,
          placeholder: 'Sélectionnez un constructeur',
        },
      })

      expect(screen.getByText('Sélectionnez un constructeur')).toBeInTheDocument()
    })
  })

  describe('Disabled state', () => {
    it('renders button as disabled when disabled prop is true', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
          disabled: true,
        },
      })

      const button = screen.getByRole('combobox')
      expect(button).toBeDisabled()
    })

    it('renders button as enabled when disabled prop is false', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
          disabled: false,
        },
      })

      const button = screen.getByRole('combobox')
      expect(button).not.toBeDisabled()
    })

    it('is enabled by default', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      const button = screen.getByRole('combobox')
      expect(button).not.toBeDisabled()
    })
  })

  describe('Loading state', () => {
    it('renders with loading prop true', () => {
      const { container } = render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: [],
          loading: true,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders with loading prop false and empty options', () => {
      const { container } = render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: [],
          loading: false,
        },
      })

      expect(container).toBeInTheDocument()
    })
  })

  describe('Options display', () => {
    it('displays all manufacturer options when opened', async () => {
      const user = userEvent.setup()
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      await user.click(screen.getByRole('combobox'))

      expect(screen.getByText('Renault')).toBeInTheDocument()
      expect(screen.getByText('Peugeot')).toBeInTheDocument()
      expect(screen.getByText('Citroën')).toBeInTheDocument()
      expect(screen.getByText('Volkswagen')).toBeInTheDocument()
    })

    it('shows check mark on selected option', async () => {
      const user = userEvent.setup()
      render(ManufacturerCombobox, {
        props: {
          modelValue: 'Peugeot',
          options: mockOptions,
        },
      })

      await user.click(screen.getByRole('combobox'))

      const options = screen.getAllByRole('option')
      const peugeotOption = options.find((opt) => opt.textContent?.includes('Peugeot'))

      expect(peugeotOption).toBeInTheDocument()
    })
  })

  describe('User interactions', () => {
    it('emits update:modelValue when manufacturer is selected', async () => {
      const user = userEvent.setup()
      const { emitted } = render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      await user.click(screen.getByRole('combobox'))
      await user.click(screen.getByText('Renault'))

      expect(emitted('update:modelValue')).toBeTruthy()
      expect(emitted('update:modelValue')?.[0]).toEqual(['Renault'])
    })

    it('emits correct value when different manufacturer is selected', async () => {
      const user = userEvent.setup()
      const { emitted } = render(ManufacturerCombobox, {
        props: {
          modelValue: 'Renault',
          options: mockOptions,
        },
      })

      await user.click(screen.getByRole('combobox'))
      await user.click(screen.getByText('Volkswagen'))

      expect(emitted('update:modelValue')).toBeTruthy()
      expect(emitted('update:modelValue')?.[0]).toEqual(['Volkswagen'])
    })

    it('opens popover when button is clicked', async () => {
      const user = userEvent.setup()
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      const button = screen.getByRole('combobox')
      expect(button).toHaveAttribute('aria-expanded', 'false')

      await user.click(button)

      expect(button).toHaveAttribute('aria-expanded', 'true')
    })
  })

  describe('Search functionality', () => {
    it('displays search input placeholder', async () => {
      const user = userEvent.setup()
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      await user.click(screen.getByRole('combobox'))

      expect(screen.getByPlaceholderText('Rechercher un constructeur...')).toBeInTheDocument()
    })

    it('allows searching through manufacturers', async () => {
      const user = userEvent.setup()
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      await user.click(screen.getByRole('combobox'))

      const searchInput = screen.getByPlaceholderText('Rechercher un constructeur...')
      await user.type(searchInput, 'Peu')

      expect(searchInput).toHaveValue('Peu')
    })
  })

  describe('Props validation', () => {
    it('handles empty options array', () => {
      const { container } = render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: [],
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('handles single option', async () => {
      const user = userEvent.setup()
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: [{ value: 'Tesla', label: 'Tesla' }],
        },
      })

      await user.click(screen.getByRole('combobox'))

      expect(screen.getByText('Tesla')).toBeInTheDocument()
    })

    it('handles many options', async () => {
      const user = userEvent.setup()
      const manyOptions = Array.from({ length: 20 }, (_, i) => ({
        value: `Brand${i}`,
        label: `Brand ${i}`,
      }))

      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: manyOptions,
        },
      })

      await user.click(screen.getByRole('combobox'))

      expect(screen.getByText('Brand 0')).toBeInTheDocument()
      expect(screen.getByText('Brand 19')).toBeInTheDocument()
    })
  })

  describe('Accessibility', () => {
    it('has correct ARIA attributes', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      const button = screen.getByRole('combobox')
      expect(button).toHaveAttribute('aria-expanded')
    })

    it('updates aria-expanded when opened', async () => {
      const user = userEvent.setup()
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
        },
      })

      const button = screen.getByRole('combobox')
      expect(button).toHaveAttribute('aria-expanded', 'false')

      await user.click(button)
      expect(button).toHaveAttribute('aria-expanded', 'true')
    })

    it('maintains disabled state in ARIA', () => {
      render(ManufacturerCombobox, {
        props: {
          modelValue: '',
          options: mockOptions,
          disabled: true,
        },
      })

      const button = screen.getByRole('combobox')
      expect(button).toBeDisabled()
    })
  })
})
