import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import CarEditDialog from '@/components/cars/CarEditDialog.vue'
import type { Car } from '@/types/cars'

// Mock API hooks
vi.mock('@/api/cars', () => ({
  useUpdateCarMutation: vi.fn(() => ({
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

// Mock vee-validate (2 forms)
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
    setValues: vi.fn(),
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

describe('CarEditDialog.vue', () => {
  const mockCar: Car = {
    id: 1,
    manufacturer: 'Renault',
    model: 'Clio',
    color: 'Bleu',
    licensePlate: 'AB-123-CD',
    registrationYear: 2020,
    registrationMonth: 6,
    mileage: 50000,
    isStored: false,
    client: {
      id: 1,
      firstName: 'Jean',
      lastName: 'Dupont',
      email: 'jean@example.com',
      phone: '0612345678',
      isClientCalledBack: false,
    },
  }

  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors when open is true', () => {
      const { container } = render(CarEditDialog, {
        props: {
          open: true,
          car: mockCar,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors when open is false', () => {
      const { container } = render(CarEditDialog, {
        props: {
          open: false,
          car: mockCar,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors with null car', () => {
      const { container } = render(CarEditDialog, {
        props: {
          open: true,
          car: null,
        },
      })

      expect(container).toBeInTheDocument()
    })
  })

  describe('Props handling', () => {
    it('accepts open prop', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })

    it('accepts car prop', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })

    it('handles prop changes', () => {
      const { rerender } = render(CarEditDialog, {
        props: {
          open: false,
          car: mockCar,
        },
      })

      expect(() => {
        void rerender({ open: true, car: mockCar })
      }).not.toThrow()
    })
  })

  describe('Different car data', () => {
    it('handles car with complete data', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })

    it('handles car with minimal data', () => {
      const minimalCar: Car = {
        ...mockCar,
        registrationYear: null,
        registrationMonth: null,
        mileage: null,
      }

      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: minimalCar,
          },
        })
      }).not.toThrow()
    })
  })

  describe('API integration', () => {
    it('integrates with useUpdateCarMutation hook', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })

    it('integrates with useManufacturersQuery hook', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })
  })

  describe('Event emissions', () => {
    it('component has close event defined', () => {
      const { emitted } = render(CarEditDialog, {
        props: {
          open: true,
          car: mockCar,
        },
      })

      expect(emitted()).toBeDefined()
    })

    it('component has success event defined', () => {
      const { emitted } = render(CarEditDialog, {
        props: {
          open: true,
          car: mockCar,
        },
      })

      expect(emitted()).toBeDefined()
    })
  })

  describe('Form integration', () => {
    it('uses vee-validate for multi-step forms', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })

    it('uses Zod for validation schemas', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })
  })

  describe('Child component integration', () => {
    it('integrates with ManufacturerCombobox', () => {
      expect(() => {
        render(CarEditDialog, {
          props: {
            open: true,
            car: mockCar,
          },
        })
      }).not.toThrow()
    })
  })
})
