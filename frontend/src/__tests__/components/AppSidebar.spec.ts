import { describe, it, expect } from 'vite-plus/test'
import { render } from '@testing-library/vue'
import '@testing-library/jest-dom'

import AppSidebar from '@/components/AppSidebar.vue'

describe('AppSidebar', () => {
  it('renders successfully', () => {
    const { getByTestId } = render(AppSidebar, {
      global: {
        stubs: {
          Sidebar: {
            template: '<div data-testid="sidebar"><slot /></div>',
          },
          SidebarContent: {
            template: '<div data-testid="sidebar-content"><slot /></div>',
          },
          NavMain: {
            template: '<div data-testid="nav-main"></div>',
          },
        },
      },
    })

    expect(getByTestId('sidebar')).toBeInTheDocument()
    expect(getByTestId('sidebar-content')).toBeInTheDocument()
    expect(getByTestId('nav-main')).toBeInTheDocument()
  })

  it('passes default collapsible prop to Sidebar', () => {
    const { getByTestId } = render(AppSidebar, {
      global: {
        stubs: {
          Sidebar: {
            template: '<div data-testid="sidebar" :data-collapsible="collapsible"><slot /></div>',
            props: ['collapsible'],
          },
          SidebarContent: true,
          NavMain: true,
        },
      },
    })

    const sidebar = getByTestId('sidebar')
    expect(sidebar).toHaveAttribute('data-collapsible', 'icon')
  })

  it('renders NavMain with correct props', () => {
    const { getByTestId } = render(AppSidebar, {
      global: {
        stubs: {
          Sidebar: {
            template: '<div data-testid="sidebar"><slot /></div>',
          },
          SidebarContent: {
            template: '<div data-testid="sidebar-content"><slot /></div>',
          },
          NavMain: {
            template: `
              <div data-testid="nav-main"
                   :data-items-count="items?.length"
                   :data-spacing="spacing">
              </div>
            `,
            props: ['items', 'spacing'],
          },
        },
      },
    })

    const navMain = getByTestId('nav-main')

    // Verify NavMain receives 5 items
    expect(navMain).toHaveAttribute('data-items-count', '5')

    // Verify NavMain receives custom spacing
    expect(navMain).toHaveAttribute(
      'data-spacing',
      'flex flex-col flex-1 justify-evenly items-center',
    )
  })
})
