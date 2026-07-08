import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import OwnerLogin from '@/views/OwnerLogin.vue'

// Mock router
vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}))

// Mock API
vi.mock('@/api/login', () => ({
  loginOwner: vi.fn(() => Promise.resolve({ token: 'fake-token' })),
}))

// Mock auth utils
vi.mock('@/utils/auth', () => ({
  setToken: vi.fn(),
}))

// Mock LoginForm component
vi.mock('@/components/LoginForm.vue', () => ({
  default: {
    name: 'LoginForm',
    template: '<div data-testid="login-form">LoginForm Mock</div>',
    props: ['userType'],
  },
}))

describe('OwnerLogin.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(OwnerLogin)

      expect(container).toBeInTheDocument()
    })

    it('renders LoginForm component', () => {
      render(OwnerLogin)

      expect(screen.getByTestId('login-form')).toBeInTheDocument()
    })

    it('does not show error message on mount', () => {
      render(OwnerLogin)

      expect(screen.queryByText(/Échec de connexion/i)).not.toBeInTheDocument()
    })

    it('does not show loading message on mount', () => {
      render(OwnerLogin)

      expect(screen.queryByText(/Connexion en cours/i)).not.toBeInTheDocument()
    })
  })

  describe('Layout', () => {
    it('has centered layout', () => {
      const { container } = render(OwnerLogin)

      const mainDiv = container.querySelector('.flex.min-h-screen')
      expect(mainDiv).toBeInTheDocument()
      expect(mainDiv?.className).toContain('items-center')
      expect(mainDiv?.className).toContain('justify-center')
    })

    it('has constrained width container', () => {
      const { container } = render(OwnerLogin)

      const formContainer = container.querySelector('.max-w-md')
      expect(formContainer).toBeInTheDocument()
    })

    it('has background styling', () => {
      const { container } = render(OwnerLogin)

      const mainDiv = container.querySelector('.bg-slate-50')
      expect(mainDiv).toBeInTheDocument()
    })
  })

  describe('LoginForm integration', () => {
    it('renders LoginForm', () => {
      render(OwnerLogin)

      expect(screen.getByTestId('login-form')).toBeInTheDocument()
    })
  })

  describe('Component structure', () => {
    it('has proper DOM structure', () => {
      const { container } = render(OwnerLogin)

      expect(container.firstChild).toBeInTheDocument()
    })

    it('contains space-y-4 container', () => {
      const { container } = render(OwnerLogin)

      const spacingContainer = container.querySelector('.space-y-4')
      expect(spacingContainer).toBeInTheDocument()
    })
  })
})
