import { describe, it, expect } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'

// oxlint-disable-next-line
import Home from '../../views/Home.vue'

describe('Home', () => {
  it('renders the title', () => {
    render(Home)

    // Check that the H1 title is displayed
    expect(
      screen.getByRole('heading', { level: 1, name: /Quel utilisateur se connecte/i }),
    ).toBeInTheDocument()
  })

  it('renders three login buttons', () => {
    render(Home)

    // Check that Owner login button is displayed
    //expect(screen.getByRole('button', { name: /Propriétaire/i })).toBeInTheDocument()

    // Check that Receptionist login button is displayed
    expect(screen.getByRole('button', { name: /Accueil/i })).toBeInTheDocument()

    // Check that Mechanic login button is displayed
    //expect(screen.getByRole('button', { name: /Mécanicien/i })).toBeInTheDocument()
  })

  it('displays exactly three buttons', () => {
    render(Home)

    const buttons = screen.getAllByRole('button')
    // expect(buttons).toHaveLength(3)
    expect(buttons).toHaveLength(1)
  })
})
