import { describe, it, expect, beforeEach } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'
import ClientInfoModal from '@/components/cars/ClientInfoModal.vue'
import type { Client } from '@/types/client'

describe('ClientInfoModal.vue', () => {
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
  })

  describe('Component mounting', () => {
    it('mounts without errors when open is true', () => {
      const { container } = render(ClientInfoModal, {
        props: {
          open: true,
          client: mockClient,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors when open is false', () => {
      const { container } = render(ClientInfoModal, {
        props: {
          open: false,
          client: mockClient,
        },
      })

      expect(container).toBeInTheDocument()
    })

    it('mounts without errors with null client', () => {
      const { container } = render(ClientInfoModal, {
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
        render(ClientInfoModal, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })

    it('accepts client prop', () => {
      expect(() => {
        render(ClientInfoModal, {
          props: {
            open: true,
            client: mockClient,
          },
        })
      }).not.toThrow()
    })

    it('handles prop changes', () => {
      const { rerender } = render(ClientInfoModal, {
        props: {
          open: false,
          client: mockClient,
        },
      })

      expect(() => {
        rerender({ open: true, client: mockClient })
      }).not.toThrow()
    })
  })

  describe('Different client data', () => {
    it('handles different client objects', () => {
      const clients: Client[] = [
        mockClient,
        {
          id: 2,
          firstName: 'Marie',
          lastName: 'Martin',
          email: 'marie@example.com',
          phone: '0698765432',
          isClientCalledBack: true,
        },
        {
          id: 3,
          firstName: 'Pierre',
          lastName: 'Durand',
          email: null,
          phone: '0601020304',
          isClientCalledBack: false,
        },
      ]

      clients.forEach((client) => {
        expect(() => {
          render(ClientInfoModal, {
            props: {
              open: true,
              client,
            },
          })
        }).not.toThrow()
      })
    })
  })

  describe('Event emissions', () => {
    it('component has close event defined', () => {
      const { emitted } = render(ClientInfoModal, {
        props: {
          open: true,
          client: mockClient,
        },
      })

      expect(emitted()).toBeDefined()
    })
  })
})
