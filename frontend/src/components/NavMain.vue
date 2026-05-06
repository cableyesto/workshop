<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next'
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'

withDefaults(
  defineProps<{
    items: {
      name: string
      url: string
      icon: LucideIcon
    }[]
    spacing?: string
  }>(),
  {
    spacing: 'space-y-1',
  },
)
</script>

<template>
  <SidebarMenu :class="spacing">
    <SidebarMenuItem v-for="item in items" :key="item.name">
      <SidebarMenuButton as-child :tooltip="item.name">
        <a :href="item.url">
          <component :is="item.icon" />
          <span>{{ item.name }}</span>
        </a>
      </SidebarMenuButton>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
