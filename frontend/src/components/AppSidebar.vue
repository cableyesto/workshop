<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'

import { House, Notebook, KeySquare, Car, Contact, Settings } from 'lucide-vue-next'
import NavMain from '@/components/NavMain.vue'

import { Sidebar, SidebarContent } from '@/components/ui/sidebar'

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: 'icon',
})

const data = {
  main: [
    {
      name: 'Garage',
      url: '/receptionist/dashboard/garage',
      icon: House,
    },
    /*{
      name: 'Planning',
      url: '#',
      icon: Notebook,
    },*/
    {
      name: 'Interventions',
      url: '/receptionist/dashboard/interventions',
      icon: KeySquare,
    },
    {
      name: 'Voitures',
      url: '/receptionist/dashboard/cars',
      icon: Car,
    },
    {
      name: 'Employés',
      url: '/receptionist/dashboard/employees',
      icon: Contact,
    },
    {
      name: 'Configuration',
      url: '/receptionist/dashboard/settings',
      icon: Settings,
    },
  ],
}
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarContent>
      <NavMain :items="data.main" spacing="flex flex-col flex-1 justify-evenly items-center" />
    </SidebarContent>
  </Sidebar>
</template>
