import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import ReceptionistDashboard from '@/views/ReceptionistDashboard.vue'

// Mock AppSidebar
vi.mock('@/components/AppSidebar.vue', () => ({
  default: {
    name: 'AppSidebar',
    template: '<div data-testid="app-sidebar">AppSidebar Mock</div>',
  },
}))

// Mock Sidebar components
vi.mock('@/components/ui/sidebar', () => ({
  SidebarProvider: {
    name: 'SidebarProvider',
    template: '<div data-testid="sidebar-provider"><slot /></div>',
    props: ['defaultOpen'],
  },
  SidebarInset: {
    name: 'SidebarInset',
    template: '<div data-testid="sidebar-inset"><slot /></div>',
  },
  SidebarTrigger: {
    name: 'SidebarTrigger',
    template: '<button data-testid="sidebar-trigger">Toggle</button>',
  },
}))

// Mock router-view
const stubs = {
  'router-view': {
    template: '<div data-testid="router-view">Router View Content</div>',
  },
}

describe('ReceptionistDashboard.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(ReceptionistDashboard, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders AppSidebar', () => {
      render(ReceptionistDashboard, {
        global: { stubs },
      })

      expect(screen.getByTestId('app-sidebar')).toBeInTheDocument()
    })

    it('renders SidebarProvider', () => {
      render(ReceptionistDashboard, {
        global: { stubs },
      })

      expect(screen.getByTestId('sidebar-provider')).toBeInTheDocument()
    })

    it('renders SidebarInset', () => {
      render(ReceptionistDashboard, {
        global: { stubs },
      })

      expect(screen.getByTestId('sidebar-inset')).toBeInTheDocument()
    })
  })

  describe('Header', () => {
    it('renders header with title', () => {
      render(ReceptionistDashboard, {
        global: { stubs },
      })

      expect(screen.getByText('Tableau de bord réception')).toBeInTheDocument()
    })

    it('renders SidebarTrigger button', () => {
      render(ReceptionistDashboard, {
        global: { stubs },
      })

      expect(screen.getByTestId('sidebar-trigger')).toBeInTheDocument()
    })

    it('has header with border styling', () => {
      const { container } = render(ReceptionistDashboard, {
        global: { stubs },
      })

      const header = container.querySelector('header.border-b')
      expect(header).toBeInTheDocument()
    })
  })

  describe('Main content', () => {
    it('renders main content area', () => {
      const { container } = render(ReceptionistDashboard, {
        global: { stubs },
      })

      const main = container.querySelector('main')
      expect(main).toBeInTheDocument()
    })

    it('renders router-view', () => {
      render(ReceptionistDashboard, {
        global: { stubs },
      })

      expect(screen.getByTestId('router-view')).toBeInTheDocument()
    })
  })

  describe('Layout structure', () => {
    it('has flex layout in main', () => {
      const { container } = render(ReceptionistDashboard, {
        global: { stubs },
      })

      const main = container.querySelector('main')
      expect(main?.className).toContain('flex')
      expect(main?.className).toContain('flex-col')
    })

    it('has proper spacing and padding', () => {
      const { container } = render(ReceptionistDashboard, {
        global: { stubs },
      })

      const main = container.querySelector('main.gap-4.p-4')
      expect(main).toBeInTheDocument()
    })
  })
})
