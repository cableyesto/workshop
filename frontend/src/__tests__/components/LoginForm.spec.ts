import { describe, it, expect } from 'vite-plus/test'
import { render, screen } from '@testing-library/vue'
import '@testing-library/jest-dom'

import LoginForm from '../../components/LoginForm.vue'

describe('LoginForm', () => {
  describe('Owner variant', () => {
    it('renders the owner title', () => {
      render(LoginForm, { props: { userType: 'owner' } })

      expect(screen.getByRole('heading', { level: 3, name: /Connexion Propriétaire/i })).toBeInTheDocument()
    })

    it('renders owner description', () => {
      render(LoginForm, { props: { userType: 'owner' } })

      expect(screen.getByText(/Entrez vos identifiants pour accéder à votre compte/i)).toBeInTheDocument()
    })

    it('renders two input fields (no SIRET)', () => {
      render(LoginForm, { props: { userType: 'owner' } })

      const emailInput = screen.getByLabelText(/Email/i)
      const passwordInput = screen.getByLabelText(/Mot de passe/i)

      expect(emailInput).toBeInTheDocument()
      expect(passwordInput).toBeInTheDocument()
      expect(screen.queryByLabelText(/SIRET/i)).not.toBeInTheDocument()
    })

    it('renders submit button', () => {
      render(LoginForm, { props: { userType: 'owner' } })

      const submitButton = screen.getByRole('button', { name: /Se connecter/i })

      expect(submitButton).toBeInTheDocument()
      expect(submitButton).toHaveAttribute('type', 'submit')
    })
  })

  describe('Receptionist variant', () => {
    it('renders the receptionist title', () => {
      render(LoginForm, { props: { userType: 'receptionist' } })

      expect(screen.getByRole('heading', { level: 3, name: /Connexion Réceptionniste/i })).toBeInTheDocument()
    })

    it('renders receptionist description', () => {
      render(LoginForm, { props: { userType: 'receptionist' } })

      expect(screen.getByText(/Entrez le SIRET du garage et vos identifiants/i)).toBeInTheDocument()
    })

    it('renders three input fields (with SIRET)', () => {
      render(LoginForm, { props: { userType: 'receptionist' } })

      const siretInput = screen.getByLabelText(/SIRET/i)
      const emailInput = screen.getByLabelText(/Email/i)
      const passwordInput = screen.getByLabelText(/Mot de passe/i)

      expect(siretInput).toBeInTheDocument()
      expect(emailInput).toBeInTheDocument()
      expect(passwordInput).toBeInTheDocument()
    })

    it('renders SIRET input with correct attributes', () => {
      render(LoginForm, { props: { userType: 'receptionist' } })

      const siretInput = screen.getByLabelText(/SIRET/i)

      expect(siretInput).toHaveAttribute('type', 'text')
      expect(siretInput).toHaveAttribute('placeholder', '12345678901234')
      expect(siretInput).toHaveAttribute('maxlength', '14')
      expect(siretInput).toBeRequired()
    })

    it('renders submit button', () => {
      render(LoginForm, { props: { userType: 'receptionist' } })

      const submitButton = screen.getByRole('button', { name: /Se connecter/i })

      expect(submitButton).toBeInTheDocument()
      expect(submitButton).toHaveAttribute('type', 'submit')
    })
  })

  describe('Common fields', () => {
    it('email input has correct attributes for both types', () => {
      const { unmount: unmountOwner } = render(LoginForm, { props: { userType: 'owner' } })
      const ownerEmailInput = screen.getByLabelText(/Email/i)

      expect(ownerEmailInput).toHaveAttribute('type', 'email')
      expect(ownerEmailInput).toHaveAttribute('placeholder', 'votre@email.com')
      expect(ownerEmailInput).toBeRequired()

      unmountOwner()

      render(LoginForm, { props: { userType: 'receptionist' } })
      const receptionistEmailInput = screen.getByLabelText(/Email/i)

      expect(receptionistEmailInput).toHaveAttribute('type', 'email')
      expect(receptionistEmailInput).toBeRequired()
    })

    it('password input has correct attributes for both types', () => {
      const { unmount: unmountOwner } = render(LoginForm, { props: { userType: 'owner' } })
      const ownerPasswordInput = screen.getByLabelText(/Mot de passe/i)

      expect(ownerPasswordInput).toHaveAttribute('type', 'password')
      expect(ownerPasswordInput).toBeRequired()

      unmountOwner()

      render(LoginForm, { props: { userType: 'receptionist' } })
      const receptionistPasswordInput = screen.getByLabelText(/Mot de passe/i)

      expect(receptionistPasswordInput).toHaveAttribute('type', 'password')
      expect(receptionistPasswordInput).toBeRequired()
    })

    it('displays exactly one button for both types', () => {
      const { unmount: unmountOwner } = render(LoginForm, { props: { userType: 'owner' } })
      expect(screen.getAllByRole('button')).toHaveLength(1)

      unmountOwner()

      render(LoginForm, { props: { userType: 'receptionist' } })
      expect(screen.getAllByRole('button')).toHaveLength(1)
    })
  })
})
