<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue'
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar'
</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
        <SidebarTrigger class="-ml-1" />
        <div class="flex items-center gap-2">
          <h1 class="text-xl font-semibold">Tableau de bord réception</h1>
        </div>
      </header>
      <main class="flex flex-1 flex-col gap-4 p-4">
        <router-view />
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
