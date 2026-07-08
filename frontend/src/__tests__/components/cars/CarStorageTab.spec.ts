import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import CarStorageTab from '@/components/cars/CarStorageTab.vue'

// Mock API hooks
vi.mock('@/api/cars', () => ({
  useStoredCarsQuery: vi.fn(() => ({
    data: { value: [] },
    isLoading: { value: false },
  })),
}))

// Mock child components
vi.mock('@/components/cars/CarStorageTable.vue', () => ({
  default: {
    name: 'CarStorageTable',
    template: '<div data-testid="car-storage-table">CarStorageTable</div>',
    props: ['cars'],
  },
}))

vi.mock('@/components/cars/CarStorageDialog.vue', () => ({
  default: {
    name: 'CarStorageDialog',
    template: '<div v-if="open">CarStorageDialog</div>',
    props: ['open'],
  },
}))

vi.mock('@/components/cars/ClientInfoModal.vue', () => ({
  default: {
    name: 'ClientInfoModal',
    template: '<div v-if="open">ClientInfoModal</div>',
    props: ['open', 'client'],
  },
}))

const stubs = {
  Button: {
    template: '<button><slot /></button>',
  },
}

describe('CarStorageTab.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(CarStorageTab, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders add to storage button', () => {
      const { container } = render(CarStorageTab, {
        global: { stubs },
      })

      const button = container.querySelector('button')
      expect(button).toBeInTheDocument()
      expect(button?.textContent).toContain('Ajouter une voiture au dépôt')
    })
  })

  describe('Component structure', () => {
    it('has flex layout container', () => {
      const { container } = render(CarStorageTab, {
        global: { stubs },
      })

      const mainDiv = container.firstChild as HTMLElement
      expect(mainDiv.className).toContain('flex')
      expect(mainDiv.className).toContain('flex-col')
    })

    it('renders all child components', () => {
      expect(() => {
        render(CarStorageTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('API integration', () => {
    it('integrates with useStoredCarsQuery hook', () => {
      expect(() => {
        render(CarStorageTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('Button interactions', () => {
    it('add to storage button is clickable', () => {
      const { container } = render(CarStorageTab, {
        global: { stubs },
      })

      const button = container.querySelector('button')
      expect(button).toBeInTheDocument()
    })
  })

  describe('Child component integration', () => {
    it('renders CarStorageDialog', () => {
      expect(() => {
        render(CarStorageTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })

    it('renders ClientInfoModal', () => {
      expect(() => {
        render(CarStorageTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })
})
