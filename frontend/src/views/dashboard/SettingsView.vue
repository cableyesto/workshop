<script setup lang="ts">
import { Power } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { logout } from '@/api/logout'

const router = useRouter()

async function handleLogout() {
  await logout()
  router.push('/')
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-3xl font-bold">Configuration</h1>

    <div class="flex flex-col gap-4">
      <div class="relative flex items-center">
        <h3 class="text-lg font-normal">Se déconnecter</h3>
        <Button
          @click="handleLogout"
          variant="destructive"
          class="absolute left-1/2 -translate-x-1/2 w-fit gap-2 bg-cyan-800 hover:bg-cyan-900"
        >
          <Power :size="20" />
          <span>Déconnexion</span>
        </Button>
      </div>
    </div>
  </div>
</template>
