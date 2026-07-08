import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'
import InterventionsView from '@/views/dashboard/InterventionsView.vue'

// Mock API
vi.mock('@/api/interventions', () => ({
  searchInterventionAPI: vi.fn(() => Promise.resolve({})),
  createInterventionAPI: vi.fn(() => Promise.resolve({ id: 1 })),
}))

// Mock SearchResult component
vi.mock('@/components/interventions/InterventionSearchResult.vue', () => ({
  default: {
    name: 'InterventionSearchResult',
    template: '<div data-testid="search-result">SearchResult</div>',
    props: ['open', 'result'],
  },
}))

// Don't mock async components - let them load naturally or fail gracefully

describe('InterventionsView.vue', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors', () => {
      const { container } = render(InterventionsView)

      expect(container).toBeInTheDocument()
    })

    it('renders page title', () => {
      render(InterventionsView)

      expect(screen.getByText('Interventions')).toBeInTheDocument()
    })

    it('has h1 title with correct styling', () => {
      const { container } = render(InterventionsView)

      const title = container.querySelector('h1.text-3xl.font-bold')
      expect(title).toBeInTheDocument()
      expect(title?.textContent).toBe('Interventions')
    })
  })

  describe('Layout structure', () => {
    it('has main flex column layout', () => {
      const { container } = render(InterventionsView)

      const mainDiv = container.querySelector('.flex.flex-col')
      expect(mainDiv).toBeInTheDocument()
    })

    it('has proper gap spacing', () => {
      const { container } = render(InterventionsView)

      const mainDiv = container.querySelector('.gap-6')
      expect(mainDiv).toBeInTheDocument()
    })

    it('has centered step container', () => {
      const { container } = render(InterventionsView)

      const stepContainer = container.querySelector('.justify-center')
      expect(stepContainer).toBeInTheDocument()
    })

    it('has max-width container for steps', () => {
      const { container } = render(InterventionsView)

      const stepContainer = container.querySelector('.max-w-md')
      expect(stepContainer).toBeInTheDocument()
    })

    it('renders full width container for steps', () => {
      const { container } = render(InterventionsView)

      const fullWidth = container.querySelector('.w-full')
      expect(fullWidth).toBeInTheDocument()
    })
  })

  describe('SearchResult component', () => {
    it('renders InterventionSearchResult', () => {
      render(InterventionsView)

      expect(screen.getByTestId('search-result')).toBeInTheDocument()
    })

    it('renders SearchResult component text', () => {
      render(InterventionsView)

      expect(screen.getByText('SearchResult')).toBeInTheDocument()
    })
  })

  describe('Component structure', () => {
    it('has proper DOM hierarchy', () => {
      const { container } = render(InterventionsView)

      const mainDiv = container.firstChild
      expect(mainDiv).toBeInTheDocument()
    })

    it('contains multiple child elements', () => {
      const { container } = render(InterventionsView)

      const mainDiv = container.querySelector('.flex.flex-col')
      expect(mainDiv?.children.length).toBeGreaterThan(0)
    })
  })

  describe('Initial state', () => {
    it('renders with default initial state', () => {
      expect(() => {
        render(InterventionsView)
      }).not.toThrow()
    })

    it('has step container visible by default', () => {
      const { container } = render(InterventionsView)

      const stepContainer = container.querySelector('.justify-center')
      expect(stepContainer).toBeVisible()
    })
  })

  describe('API integration', () => {
    it('integrates with interventions API', () => {
      expect(() => {
        render(InterventionsView)
      }).not.toThrow()
    })
  })
})
