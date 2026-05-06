import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/Home.vue'),
    },
    {
      path: '/owner/login',
      name: 'owner-login',
      component: () => import('@/views/OwnerLogin.vue'),
    },
    {
      path: '/receptionist/login',
      name: 'receptionist-login',
      component: () => import('@/views/ReceptionistLogin.vue'),
    },
    {
      path: '/receptionist/dashboard',
      name: 'receptionist-dashboard',
      component: () => import('@/views/ReceptionistDashboard.vue'),
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
          path: 'configuration',
          name: 'receptionist-configuration',
          component: () => import('@/views/dashboard/ConfigurationView.vue'),
        },
      ],
    },
  ],
})

export default router
