import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import userEvent from '@testing-library/user-event'
import Home from '@/views/Home.vue'

// Mock router
const mockPush = vi.fn()
vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: mockPush,
  })),
}))

const stubs = {
  Button: {
    template: '<button><slot /></button>',
  },
}

describe('Home.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Rendering', () => {
    it('renders the main title', () => {
      render(Home, {
        global: { stubs },
      })

      expect(screen.getByText('Wörkshop')).toBeInTheDocument()
    })

    it('renders the question', () => {
      render(Home, {
        global: { stubs },
      })

      expect(screen.getByText('Quel utilisateur se connecte ?')).toBeInTheDocument()
    })

    it('renders the receptionist button', () => {
      render(Home, {
        global: { stubs },
      })

      expect(screen.getByText('Accueil')).toBeInTheDocument()
    })

    it('displays exactly one button', () => {
      render(Home, {
        global: { stubs },
      })

      const buttons = screen.getAllByRole('button')
      expect(buttons).toHaveLength(1)
    })
  })

  describe('Layout', () => {
    it('has centered layout', () => {
      const { container } = render(Home, {
        global: { stubs },
      })

      const mainDiv = container.querySelector('.flex.h-full.min-h-screen')
      expect(mainDiv).toBeInTheDocument()
      expect(mainDiv?.className).toContain('items-center')
      expect(mainDiv?.className).toContain('justify-center')
    })
  })

  describe('Navigation', () => {
    it('navigates to receptionist login when button clicked', async () => {
      const user = userEvent.setup()
      render(Home, {
        global: { stubs },
      })

      await user.click(screen.getByText('Accueil'))

      expect(mockPush).toHaveBeenCalledWith('/receptionist/login')
    })

    it('uses router push for navigation', async () => {
      const user = userEvent.setup()
      render(Home, {
        global: { stubs },
      })

      await user.click(screen.getByText('Accueil'))

      expect(mockPush).toHaveBeenCalledTimes(1)
    })
  })

  describe('Buttons', () => {
    it('receptionist button is clickable', () => {
      render(Home, {
        global: { stubs },
      })

      const button = screen.getByText('Accueil')
      expect(button.tagName).toBe('BUTTON')
    })
  })

  describe('Component structure', () => {
    it('mounts without errors', () => {
      const { container } = render(Home, {
        global: { stubs },
      })

      expect(container).toBeInTheDocument()
    })
  })
})
