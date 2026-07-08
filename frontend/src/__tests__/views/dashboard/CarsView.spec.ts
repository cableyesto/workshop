import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import CarsView from '@/views/dashboard/CarsView.vue'

// Mock Tab components
vi.mock('@/components/cars/CarRecordTab.vue', () => ({
  default: {
    name: 'CarRecordTab',
    template: '<div data-testid="car-record-tab">CarRecordTab</div>',
  },
}))

vi.mock('@/components/cars/CarStorageTab.vue', () => ({
  default: {
    name: 'CarStorageTab',
    template: '<div data-testid="car-storage-tab">CarStorageTab</div>',
  },
}))

vi.mock('@/components/cars/CarInterventionTab.vue', () => ({
  default: {
    name: 'CarInterventionTab',
    template: '<div data-testid="car-intervention-tab">CarInterventionTab</div>',
  },
}))

vi.mock('@/components/cars/CarRestitutionTab.vue', () => ({
  default: {
    name: 'CarRestitutionTab',
    template: '<div data-testid="car-restitution-tab">CarRestitutionTab</div>',
  },
}))

// Mock Tabs components
const stubs = {
  Tabs: {
    template: '<div data-testid="tabs"><slot /></div>',
    props: ['modelValue', 'defaultValue'],
  },
  TabsList: {
    template: '<div data-testid="tabs-list"><slot /></div>',
  },
  TabsTrigger: {
    template: '<button data-testid="tab-trigger"><slot /></button>',
    props: ['value'],
  },
  TabsContent: {
    template: '<div data-testid="tab-content"><slot /></div>',
    props: ['value'],
  },
}

describe('CarsView.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(CarsView, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders page title', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByText('Voitures')).toBeInTheDocument()
    })

    it('renders Tabs component', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByTestId('tabs')).toBeInTheDocument()
    })
  })

  describe('Tabs structure', () => {
    it('renders TabsList', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByTestId('tabs-list')).toBeInTheDocument()
    })

    it('renders all tab triggers', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByText('Fiches')).toBeInTheDocument()
      expect(screen.getByText('Dépôt')).toBeInTheDocument()
      expect(screen.getByText('Intervention')).toBeInTheDocument()
      expect(screen.getByText('Restitution')).toBeInTheDocument()
    })

    it('renders correct number of tab triggers', () => {
      render(CarsView, {
        global: { stubs },
      })

      const triggers = screen.getAllByTestId('tab-trigger')
      expect(triggers).toHaveLength(4)
    })
  })

  describe('Tab content', () => {
    it('renders all TabsContent', () => {
      render(CarsView, {
        global: { stubs },
      })

      const contents = screen.getAllByTestId('tab-content')
      expect(contents).toHaveLength(4)
    })

    it('renders CarRecordTab', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByTestId('car-record-tab')).toBeInTheDocument()
    })

    it('renders CarStorageTab', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByTestId('car-storage-tab')).toBeInTheDocument()
    })

    it('renders CarInterventionTab', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByTestId('car-intervention-tab')).toBeInTheDocument()
    })

    it('renders CarRestitutionTab', () => {
      render(CarsView, {
        global: { stubs },
      })

      expect(screen.getByTestId('car-restitution-tab')).toBeInTheDocument()
    })
  })

  describe('Layout', () => {
    it('has flex column layout', () => {
      const { container } = render(CarsView, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.flex.flex-col')
      expect(mainDiv).toBeInTheDocument()
    })

    it('has proper gap spacing', () => {
      const { container } = render(CarsView, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.gap-6')
      expect(mainDiv).toBeInTheDocument()
    })
  })
})
