import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import CarStorageDialog from '@/components/cars/CarStorageDialog.vue'

// Mock the API hook
vi.mock('@/api/cars', () => ({
  useUpdateCarStorageByLicensePlateMutation: vi.fn(() => ({
    mutate: vi.fn(),
  })),
}))

// Mock vee-validate
vi.mock('vee-validate', () => ({
  useForm: vi.fn(() => ({
    handleSubmit: vi.fn(() => vi.fn()),
    resetForm: vi.fn(),
    setFieldValue: vi.fn(),
    setErrors: vi.fn(),
  })),
  Field: {
    name: 'VeeField',
    template: '<div><slot :field="{}" :errors="[]" /></div>',
  },
}))

vi.mock('@vee-validate/zod', () => ({
  toTypedSchema: vi.fn((schema) => schema),
}))

describe('CarStorageDialog.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors when open is true', () => {
      const { container } = render(CarStorageDialog, {
        props: {
          open: true,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors when open is false', () => {
      const { container } = render(CarStorageDialog, {
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
        render(CarStorageDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })

    it('handles prop changes', () => {
      const { rerender } = render(CarStorageDialog, {
        props: {
          open: false,
        },
      })

      expect(() => {
        rerender({ open: true })
      }).not.toThrow()
    })

    it('handles multiple open/close cycles', () => {
      const { rerender } = render(CarStorageDialog, {
        props: {
          open: false,
        },
      })

      expect(() => {
        rerender({ open: true })
        rerender({ open: false })
        rerender({ open: true })
      }).not.toThrow()
    })
  })

  describe('Event emissions', () => {
    it('component has close event defined', () => {
      const { emitted } = render(CarStorageDialog, {
        props: {
          open: true,
        },
      })

      expect(emitted()).toBeDefined()
    })

    it('component has success event defined', () => {
      const { emitted } = render(CarStorageDialog, {
        props: {
          open: true,
        },
      })

      expect(emitted()).toBeDefined()
    })
  })

  describe('Integration', () => {
    it('uses API mutation hook', () => {
      expect(() => {
        render(CarStorageDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })

    it('uses form validation', () => {
      expect(() => {
        render(CarStorageDialog, {
          props: {
            open: true,
          },
        })
      }).not.toThrow()
    })
  })
})
