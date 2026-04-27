<script setup lang="ts">
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'

const router = useRouter()

const navigateToOwnerLogin = () => {
  router.push('/owner/login')
}

const navigateToReceptionistLogin = () => {
  router.push('/receptionist/login')
}

const navigateToMechanicLogin = () => {
  router.push('/mechanic/login')
}
</script>

<template>
  <div class="flex h-full min-h-screen items-center justify-center bg-slate-50">
    <div class="flex flex-col items-center gap-12">
      <h1 class="text-4xl font-bold text-gray-900">
        Quel utilisateur se connecte ?
      </h1>

      <div class="flex gap-6">
        <Button
          class="h-24 w-48 bg-cyan-800 text-lg font-semibold hover:bg-cyan-900"
          @click="navigateToOwnerLogin"
        >
          Propriétaire
        </Button>

        <Button
          class="h-24 w-48 bg-[#3e8eab] text-lg font-semibold hover:bg-[#357a92]"
          @click="navigateToReceptionistLogin"
        >
          Accueil
        </Button>

        <Button
          class="h-24 w-48 bg-cyan-800 text-lg font-semibold hover:bg-cyan-900"
          @click="navigateToMechanicLogin"
        >
          Mécanicien
        </Button>
      </div>
    </div>
  </div>
</template>
