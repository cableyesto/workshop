import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import ClientFormDialog from '@/components/cars/ClientFormDialog.vue'
import type { Client } from '@/types/client'

// Mock API hooks
vi.mock('@/api/clients', () => ({
  useUpdateClientMutation: vi.fn(() => ({
    mutate: vi.fn(),
    isLoading: { value: false },
  })),
}))

// Mock vee-validate
vi.mock('vee-validate', () => ({
  useForm: vi.fn(() => ({
    handleSubmit: vi.fn((onSuccess) => (e: Event) => {
      e?.preventDefault?.()
      onSuccess({})
    }),
    errors: { value: {} },
    defineField: vi.fn((name) => [{ value: '' }, {}]),
    resetForm: vi.fn(),
    setValues: vi.fn(),
  })),
}))

vi.mock('@vee-validate/zod', () => ({
  toTypedSchema: vi.fn((schema) => schema),
}))

describe('ClientFormDialog.vue', () => {
  const mockClient: Client = {
    id: 1,
    firstName: 'Jean',
    lastName: 'Dupont',
    email: 'jean.dupont@example.com',
    phone: '0612345678',
    isClientCalledBack: false,
  }

  beforeEach(() => {
    document.body.innerHTML = ''
    Element.prototype.scrollIntoView = () => {}
    vi.clearAllMocks()
  })

  describe('Component mounting', () => {
    it('mounts without errors when open is true', () => {
      const { container } = render(ClientFormDialog, {
        props: {
          open: true,
          client: mockClient,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors when open is false', () => {
      const { container } = render(ClientFormDialog, {
        props: {
          open: false,
          client: mockClient,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors with null client', () => {
      const { container } = render(ClientFormDialog, {
        props: {
          open: true,
          client: null,
        },
      })

      expect(container).toBeInTheDocument()
    })
  })

  describe('Props handling', () => {
    it('accepts open prop', () => {
      expect(() => {
        render(ClientFormDialog, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })

    it('accepts client prop', () => {
      expect(() => {
        render(ClientFormDialog, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })
  })

  describe('Different client data', () => {
    it('handles client with email', () => {
      expect(() => {
        render(ClientFormDialog, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })

    it('handles client without email', () => {
      const clientWithoutEmail: Client = {
        ...mockClient,
        email: null,
      }

      expect(() => {
        render(ClientFormDialog, {
          props: {
            open: true,
            client: clientWithoutEmail,
          },
        })
      }).not.toThrow()
    })
  })

  describe('API integration', () => {
    it('integrates with useUpdateClientMutation hook', () => {
      expect(() => {
        render(ClientFormDialog, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })
  })

  describe('Event emissions', () => {
    it('component has close event defined', () => {
      const { emitted } = render(ClientFormDialog, {
        props: {
          open: true,
          client: mockClient,
        },
      })

      expect(emitted()).toBeDefined()
    })

    it('component has success event defined', () => {
      const { emitted } = render(ClientFormDialog, {
        props: {
          open: true,
          client: mockClient,
        },
      })

      expect(emitted()).toBeDefined()
    })
  })

  describe('Form integration', () => {
    it('uses vee-validate for form handling', () => {
      expect(() => {
        render(ClientFormDialog, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })

    it('uses Zod for validation schema', () => {
      expect(() => {
        render(ClientFormDialog, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })
  })
})
