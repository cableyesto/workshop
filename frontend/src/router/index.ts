import { createRouter, createWebHistory } from 'vue-router'
import { isAuthenticated, hasRole } from '@/utils/auth'

// Augment vue-router types for meta fields
declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    roles?: string[]
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/Home.vue'),
      meta: { requiresAuth: false },
    },
    {
      path: '/owner/login',
      name: 'owner-login',
      component: () => import('@/views/OwnerLogin.vue'),
      meta: { requiresAuth: false },
    },
    {
      path: '/receptionist/login',
      name: 'receptionist-login',
      component: () => import('@/views/ReceptionistLogin.vue'),
      meta: { requiresAuth: false },
    },
    {
      path: '/receptionist/dashboard',
      name: 'receptionist-dashboard',
      component: () => import('@/views/ReceptionistDashboard.vue'),
      meta: { requiresAuth: true, roles: ['ROLE_RECEPTIONIST', 'ROLE_OWNER'] },
      children: [
        {
          path: '',
          redirect: '/receptionist/dashboard/garage',
        },
        {
          path: 'garage',
          name: 'receptionist-garage',
          component: () => import('@/views/dashboard/GarageView.vue'),
        },
        {
          path: 'interventions',
          name: 'receptionist-interventions',
          component: () => import('@/views/dashboard/InterventionsView.vue'),
        },
        {
          path: 'cars',
          name: 'receptionist-cars',
          component: () => import('@/views/dashboard/CarsView.vue'),
        },
        {
          path: 'employees',
          name: 'receptionist-employees',
          component: () => import('@/views/dashboard/EmployeesView.vue'),
        },
        {
          path: 'settings',
          name: 'receptionist-settings',
          component: () => import('@/views/dashboard/SettingsView.vue'),
        },
      ],
    },
  ],
})

// Navigation guard - protect routes requiring authentication and roles
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const requiredRoles = to.meta.roles as string[] | undefined

  // Check authentication
  if (requiresAuth && !isAuthenticated()) {
    next('/')
    return
  }

  // Check role authorization
  if (requiredRoles && requiredRoles.length > 0) {
    const hasRequiredRole = requiredRoles.some((role) => hasRole(role))
    if (!hasRequiredRole) {
      next('/') // Has JWT but wrong role - redirect to home
      return
    }
  }

  next()
})

export default router
