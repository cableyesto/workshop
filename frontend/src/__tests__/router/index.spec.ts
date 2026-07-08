import { describe, it, expect } from 'vite-plus/test'
import router from '@/router'

describe('router', () => {
  describe('Route Configuration', () => {
    it('has home route', () => {
      const route = router.getRoutes().find((r) => r.name === 'home')

      expect(route).toBeDefined()
      expect(route?.path).toBe('/')
      expect(route?.meta.requiresAuth).toBe(false)
    })

    it('has receptionist-login route', () => {
      const route = router.getRoutes().find((r) => r.name === 'receptionist-login')

      expect(route).toBeDefined()
      expect(route?.path).toBe('/receptionist/login')
      expect(route?.meta.requiresAuth).toBe(false)
    })

    it('has receptionist-dashboard route with auth requirement', () => {
      const route = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')

      expect(route).toBeDefined()
      expect(route?.path).toBe('/receptionist/dashboard')
      expect(route?.meta.requiresAuth).toBe(true)
      expect(route?.meta.roles).toEqual(['ROLE_RECEPTIONIST', 'ROLE_OWNER'])
    })

    it('has all dashboard child routes', () => {
      const dashboard = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')

      expect(dashboard?.children).toBeDefined()
      expect(dashboard?.children?.length).toBeGreaterThan(0)

      const childNames = dashboard?.children?.map((c) => c.name)
      expect(childNames).toContain('receptionist-garage')
      expect(childNames).toContain('receptionist-interventions')
      expect(childNames).toContain('receptionist-cars')
      expect(childNames).toContain('receptionist-employees')
      expect(childNames).toContain('receptionist-settings')
    })

    it('has receptionist-garage child route', () => {
      const dashboard = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')
      const garage = dashboard?.children?.find((c) => c.name === 'receptionist-garage')

      expect(garage).toBeDefined()
      expect(garage?.path).toBe('garage')
    })

    it('has receptionist-interventions child route', () => {
      const dashboard = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')
      const interventions = dashboard?.children?.find(
        (c) => c.name === 'receptionist-interventions',
      )

      expect(interventions).toBeDefined()
      expect(interventions?.path).toBe('interventions')
    })

    it('has receptionist-cars child route', () => {
      const dashboard = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')
      const cars = dashboard?.children?.find((c) => c.name === 'receptionist-cars')

      expect(cars).toBeDefined()
      expect(cars?.path).toBe('cars')
    })

    it('has receptionist-employees child route', () => {
      const dashboard = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')
      const employees = dashboard?.children?.find((c) => c.name === 'receptionist-employees')

      expect(employees).toBeDefined()
      expect(employees?.path).toBe('employees')
    })

    it('has receptionist-settings child route', () => {
      const dashboard = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')
      const settings = dashboard?.children?.find((c) => c.name === 'receptionist-settings')

      expect(settings).toBeDefined()
      expect(settings?.path).toBe('settings')
    })
  })

  describe('Route Meta', () => {
    it('marks public routes correctly', () => {
      const publicRoutes = ['home', 'receptionist-login']

      publicRoutes.forEach((routeName) => {
        const route = router.getRoutes().find((r) => r.name === routeName)
        expect(route?.meta.requiresAuth).toBe(false)
      })
    })

    it('marks protected routes correctly', () => {
      const protectedRoute = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')

      expect(protectedRoute?.meta.requiresAuth).toBe(true)
    })

    it('has correct roles for receptionist dashboard', () => {
      const route = router.getRoutes().find((r) => r.name === 'receptionist-dashboard')

      expect(route?.meta.roles).toBeDefined()
      expect(route?.meta.roles).toContain('ROLE_RECEPTIONIST')
      expect(route?.meta.roles).toContain('ROLE_OWNER')
    })
  })

  describe('Router Instance', () => {
    it('has correct base configuration', () => {
      expect(router).toBeDefined()
      expect(router.getRoutes().length).toBeGreaterThan(0)
    })

    it('has all expected routes', () => {
      const routes = router.getRoutes()
      const routeNames = routes.map((r) => r.name).filter(Boolean)

      expect(routeNames).toContain('home')
      expect(routeNames).toContain('receptionist-login')
      expect(routeNames).toContain('receptionist-dashboard')
    })
  })
})
