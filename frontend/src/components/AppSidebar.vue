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
      url: '#',
      icon: House,
    },
    /*{
      name: 'Planning',
      url: '#',
      icon: Notebook,
    },*/
    {
      name: 'Interventions',
      url: '#',
      icon: KeySquare,
    },
    {
      name: 'Voitures',
      url: '#',
      icon: Car,
    },
    {
      name: 'Employés',
      url: '#',
      icon: Contact,
    },
    {
      name: 'Configuration',
      url: '#',
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
