import { describe, it, expect } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'

import NavMain from '@/components/NavMain.vue'

// Mock Lucide icon component
const MockIcon = {
  name: 'MockIcon',
  template: '<svg data-testid="mock-icon" />',
}

// Test data
const mockItems = [
  { name: 'Garage', url: '/garage', icon: MockIcon },
  { name: 'Voitures', url: '/cars', icon: MockIcon },
  { name: 'Employés', url: '/employees', icon: MockIcon },
]

// Helper to render component with stubs
function renderNavMain(props = {}) {
  return render(NavMain, {
    props: {
      items: mockItems,
      ...props,
    },
    global: {
      stubs: {
        'router-link': {
          template: '<a :href="to"><slot /></a>',
          props: ['to'],
        },
        SidebarMenu: {
          template: '<div class="sidebar-menu" :class="$attrs.class"><slot /></div>',
        },
        SidebarMenuItem: {
          template: '<div class="sidebar-item"><slot /></div>',
        },
        SidebarMenuButton: {
          template: '<div class="sidebar-button"><slot /></div>',
        },
      },
    },
  })
}

describe('NavMain', () => {
  it('renders menu with correct number of items', () => {
    const { container } = renderNavMain()

    const menuItems = container.querySelectorAll('.sidebar-item')
    expect(menuItems).toHaveLength(3)
  })

  it('displays all item names correctly', () => {
    renderNavMain()

    expect(screen.getByText('Garage')).toBeInTheDocument()
    expect(screen.getByText('Voitures')).toBeInTheDocument()
    expect(screen.getByText('Employés')).toBeInTheDocument()
  })

  it('creates router links with correct URLs', () => {
    renderNavMain()

    const links = screen.getAllByRole('link')
    expect(links).toHaveLength(3)
    expect(links[0]).toHaveAttribute('href', '/garage')
    expect(links[1]).toHaveAttribute('href', '/cars')
    expect(links[2]).toHaveAttribute('href', '/employees')
  })

  it('renders icon for each item', () => {
    const { container } = renderNavMain()

    const icons = container.querySelectorAll('[data-testid="mock-icon"]')
    expect(icons).toHaveLength(3)
  })

  it('applies default spacing class', () => {
    const { container } = renderNavMain()

    const menu = container.querySelector('.sidebar-menu')
    expect(menu).toHaveClass('space-y-1')
  })
})
