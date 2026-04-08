import { describe, it, expect } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'

import App from '../App.vue'
describe('App', () => {
  it('renders properly', async () => {
    render(App)
    // Check that the text appears in the document
    expect(screen.getByText('You did it!')).toBeInTheDocument()
  })
})
