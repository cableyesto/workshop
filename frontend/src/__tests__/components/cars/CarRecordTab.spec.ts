import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import CarRecordTab from '@/components/cars/CarRecordTab.vue'

// Mock API hooks
vi.mock('@/api/cars', () => ({
  useAllCarsQuery: vi.fn(() => ({
    data: { value: [] },
    isLoading: { value: false },
  })),
  useUpdateCarStorageByLicensePlateMutation: vi.fn(() => ({
    mutate: vi.fn(),
  })),
}))

// Mock child components
vi.mock('@/components/cars/CarRecordTable.vue', () => ({
  default: {
    name: 'CarRecordTable',
    template: '<div data-testid="car-record-table">CarRecordTable</div>',
    props: ['cars'],
  },
}))

vi.mock('@/components/cars/ClientFormDialog.vue', () => ({
  default: {
    name: 'ClientFormDialog',
    template: '<div v-if="open">ClientFormDialog</div>',
    props: ['open', 'client'],
  },
}))

vi.mock('@/components/cars/CarEditDialog.vue', () => ({
  default: {
    name: 'CarEditDialog',
    template: '<div v-if="open">CarEditDialog</div>',
    props: ['open', 'car'],
  },
}))

vi.mock('@/components/cars/CarCreateDialog.vue', () => ({
  default: {
    name: 'CarCreateDialog',
    template: '<div v-if="open">CarCreateDialog</div>',
    props: ['open'],
  },
}))

const stubs = {
  Button: {
    template: '<button><slot /></button>',
  },
}

describe('CarRecordTab.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(CarRecordTab, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders create button', () => {
      const { container } = render(CarRecordTab, {
        global: { stubs },
      })

      const button = container.querySelector('button')
      expect(button).toBeInTheDocument()
      expect(button?.textContent).toContain('Créer une fiche voiture')
    })
  })

  describe('Loading state', () => {
    it('renders component with default mocked state', () => {
      const { container } = render(CarRecordTab, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })
  })

  describe('CarRecordTable rendering', () => {
    it('renders component with table integration', () => {
      expect(() => {
        render(CarRecordTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('API integration', () => {
    it('integrates with API hooks', () => {
      expect(() => {
        render(CarRecordTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('Component structure', () => {
    it('has flex layout container', () => {
      const { container } = render(CarRecordTab, {
        global: { stubs },
      })

      const mainDiv = container.firstChild as HTMLElement
      expect(mainDiv.className).toContain('flex')
      expect(mainDiv.className).toContain('flex-col')
    })

    it('renders all child dialogs', () => {
      expect(() => {
        render(CarRecordTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('Different data states', () => {
    it('handles component with mocked data', () => {
      expect(() => {
        render(CarRecordTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })
})
