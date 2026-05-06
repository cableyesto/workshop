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
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
          <div class="aspect-video rounded-xl bg-slate-100" />
          <div class="aspect-video rounded-xl bg-slate-100" />
          <div class="aspect-video rounded-xl bg-slate-100" />
        </div>
        <div class="min-h-screen flex-1 rounded-xl bg-slate-100 md:min-h-min" />
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
