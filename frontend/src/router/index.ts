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
  ],
})

export default router
