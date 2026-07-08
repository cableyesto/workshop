import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import SettingsView from '@/views/dashboard/SettingsView.vue'

// Mock router
vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}))

// Mock logout API
vi.mock('@/api/logout', () => ({
  logout: vi.fn(() => Promise.resolve()),
}))

// Mock Power icon
vi.mock('lucide-vue-next', () => ({
  Power: {
    name: 'Power',
    template: '<svg data-testid="power-icon"></svg>',
    props: ['size'],
  },
}))

const stubs = {
  Button: {
    template: '<button><slot /></button>',
    props: ['variant'],
  },
}

describe('SettingsView.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })

    it('renders page title', () => {
      render(SettingsView, {
        global: { stubs },
      })

      expect(screen.getByText('Configuration')).toBeInTheDocument()
    })

    it('has h1 title with correct styling', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const title = container.querySelector('h1.text-3xl.font-bold')
      expect(title).toBeInTheDocument()
    })
  })

  describe('Logout section', () => {
    it('renders logout section title', () => {
      render(SettingsView, {
        global: { stubs },
      })

      expect(screen.getByText('Se déconnecter')).toBeInTheDocument()
    })

    it('has h3 section title with correct styling', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const sectionTitle = container.querySelector('h3.text-lg')
      expect(sectionTitle).toBeInTheDocument()
      expect(sectionTitle?.textContent).toBe('Se déconnecter')
    })

    it('renders logout button', () => {
      render(SettingsView, {
        global: { stubs },
      })

      expect(screen.getByRole('button', { name: /déconnexion/i })).toBeInTheDocument()
    })

    it('logout button has correct text', () => {
      render(SettingsView, {
        global: { stubs },
      })

      expect(screen.getByText('Déconnexion')).toBeInTheDocument()
    })

    it('renders Power icon', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      expect(container.querySelector('[data-testid="power-icon"]')).toBeInTheDocument()
    })
  })

  describe('Layout structure', () => {
    it('has main flex column layout', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.flex.flex-col')
      expect(mainDiv).toBeInTheDocument()
    })

    it('has proper gap spacing on main container', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.gap-6')
      expect(mainDiv).toBeInTheDocument()
    })

    it('has gap spacing on settings section', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const settingsSection = container.querySelector('.gap-4')
      expect(settingsSection).toBeInTheDocument()
    })

    it('has relative positioning for logout section', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const logoutSection = container.querySelector('.relative')
      expect(logoutSection).toBeInTheDocument()
    })

    it('has flex items alignment', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const logoutSection = container.querySelector('.items-center')
      expect(logoutSection).toBeInTheDocument()
    })
  })

  describe('Component structure', () => {
    it('has proper DOM hierarchy', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      expect(container.firstChild).toBeInTheDocument()
    })

    it('contains all main sections', () => {
      const { container } = render(SettingsView, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.flex.flex-col')
      expect(mainDiv?.children.length).toBeGreaterThanOrEqual(2)
    })
  })
})
