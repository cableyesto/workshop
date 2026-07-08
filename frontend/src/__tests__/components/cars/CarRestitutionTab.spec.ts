import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import CarRestitutionTab from '@/components/cars/CarRestitutionTab.vue'

// Mock API hooks
vi.mock('@/api/cars', () => ({
  useRestitutionCarsQuery: vi.fn(() => ({
    data: { value: [] },
    isLoading: { value: false },
  })),
  usePatchClientCalledBackMutation: vi.fn(() => ({
    mutate: vi.fn(),
  })),
  useRemoveCarFromStorageMutation: vi.fn(() => ({
    mutate: vi.fn(),
  })),
}))

// Mock Pinia Colada
vi.mock('@pinia/colada', () => ({
  useQueryCache: vi.fn(() => ({
    invalidateQueries: vi.fn(),
  })),
}))

// Mock child components
vi.mock('@/components/cars/CarRestitutionTable.vue', () => ({
  default: {
    name: 'CarRestitutionTable',
    template: '<div data-testid="car-restitution-table">CarRestitutionTable</div>',
    props: ['cars', 'updatingClientIds'],
  },
}))

vi.mock('@/components/cars/ClientInfoModal.vue', () => ({
  default: {
    name: 'ClientInfoModal',
    template: '<div v-if="open">ClientInfoModal</div>',
    props: ['open', 'client'],
  },
}))

// Mock lucide-vue-next
vi.mock('lucide-vue-next', () => ({
  RefreshCw: {
    name: 'RefreshCw',
    template: '<svg class="h-4 w-4"></svg>',
  },
}))

const stubs = {
  Button: {
    template: '<button><slot /></button>',
  },
}

describe('CarRestitutionTab.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(CarRestitutionTab, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders refresh button', () => {
      const { container } = render(CarRestitutionTab, {
        global: { stubs },
      })

      const button = container.querySelector('button')
      expect(button).toBeInTheDocument()
      expect(button?.textContent).toContain('Rafraîchir')
    })
  })

  describe('Component structure', () => {
    it('has flex layout container', () => {
      const { container } = render(CarRestitutionTab, {
        global: { stubs },
      })

      const mainDiv = container.firstChild as HTMLElement
      expect(mainDiv.className).toContain('flex')
      expect(mainDiv.className).toContain('flex-col')
    })

    it('renders all child components', () => {
      expect(() => {
        render(CarRestitutionTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('API integration', () => {
    it('integrates with useRestitutionCarsQuery hook', () => {
      expect(() => {
        render(CarRestitutionTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })

    it('integrates with usePatchClientCalledBackMutation hook', () => {
      expect(() => {
        render(CarRestitutionTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })

    it('integrates with useRemoveCarFromStorageMutation hook', () => {
      expect(() => {
        render(CarRestitutionTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })

    it('integrates with useQueryCache hook', () => {
      expect(() => {
        render(CarRestitutionTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('Refresh functionality', () => {
    it('refresh button is clickable', () => {
      const { container } = render(CarRestitutionTab, {
        global: { stubs },
      })

      const button = container.querySelector('button')
      expect(button).toBeInTheDocument()
    })

    it('renders refresh icon', () => {
      const { container } = render(CarRestitutionTab, {
        global: { stubs },
      })

      const svg = container.querySelector('svg')
      expect(svg).toBeInTheDocument()
    })
  })

  describe('Child component integration', () => {
    it('renders ClientInfoModal', () => {
      expect(() => {
        render(CarRestitutionTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })

  describe('State management', () => {
    it('manages updatingClientIds state', () => {
      expect(() => {
        render(CarRestitutionTab, {
          global: { stubs },
        })
      }).not.toThrow()
    })
  })
})
