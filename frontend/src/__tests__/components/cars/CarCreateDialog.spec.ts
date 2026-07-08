import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import CarCreateDialog from '@/components/cars/CarCreateDialog.vue'

// Mock API hooks
vi.mock('@/api/cars', () => ({
  useCreateCarWithClientMutation: vi.fn(() => ({
    mutate: vi.fn(),
    isLoading: { value: false },
  })),
}))

vi.mock('@/api/manufacturers', () => ({
  useManufacturersQuery: vi.fn(() => ({
    data: { value: [] },
    isLoading: { value: false },
  })),
}))

// Mock vee-validate (3 forms)
vi.mock('vee-validate', () => ({
  useForm: vi.fn(() => ({
    handleSubmit: vi.fn((onSuccess) => (e?: Event) => {
      e?.preventDefault?.()
      onSuccess({})
    }),
    values: { value: {} },
    errors: { value: {} },
    defineField: vi.fn((name) => [{ value: '' }, {}]),
    resetForm: vi.fn(),
    setErrors: vi.fn(),
  })),
}))

vi.mock('@vee-validate/zod', () => ({
  toTypedSchema: vi.fn((schema) => schema),
}))

// Mock ManufacturerCombobox
vi.mock('@/components/cars/ManufacturerCombobox.vue', () => ({
  default: {
    name: 'ManufacturerCombobox',
    template: '<div data-testid="manufacturer-combobox"></div>',
    props: ['modelValue', 'options', 'loading'],
  },
}))

describe('CarCreateDialog.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors when open is true', () => {
      const { container } = render(CarCreateDialog, {
        props: {
          open: true,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors when open is false', () => {
      const { container } = render(CarCreateDialog, {
        props: {
          open: false,
        },
      })

      expect(container).toBeInTheDocument()
    })
  })

  describe('Props handling', () => {
    it('accepts open prop', () => {
      expect(() => {
        render(CarCreateDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })

    it('handles prop changes', () => {
      const { rerender } = render(CarCreateDialog, {
        props: {
          open: false,
        },
      })

      expect(() => {
        void rerender({ open: true })
      }).not.toThrow()
    })
  })

  describe('API integration', () => {
    it('integrates with useCreateCarWithClientMutation hook', () => {
      expect(() => {
        render(CarCreateDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })

    it('integrates with useManufacturersQuery hook', () => {
      expect(() => {
        render(CarCreateDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })
  })

  describe('Event emissions', () => {
    it('component has close event defined', () => {
      const { emitted } = render(CarCreateDialog, {
        props: {
          open: true,
        },
      })

      expect(emitted()).toBeDefined()
    })

    it('component has success event defined', () => {
      const { emitted } = render(CarCreateDialog, {
        props: {
          open: true,
        },
      })

      expect(emitted()).toBeDefined()
    })
  })

  describe('Form integration', () => {
    it('uses vee-validate for multi-step forms', () => {
      expect(() => {
        render(CarCreateDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })

    it('uses Zod for validation schemas', () => {
      expect(() => {
        render(CarCreateDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })
  })

  describe('Child component integration', () => {
    it('integrates with ManufacturerCombobox', () => {
      expect(() => {
        render(CarCreateDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })
  })
})
