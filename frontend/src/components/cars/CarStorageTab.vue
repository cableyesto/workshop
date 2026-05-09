<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import CarStorageTable from './CarStorageTable.vue'
import type { StoredCar, Client } from '../../types/cars'

// Mock data for now - will be replaced with useQuery later
const storedCars = ref<StoredCar[]>([])
const isStorageDialogOpen = ref(false)

function handleAddCarToStorage() {
  isStorageDialogOpen.value = true
}

function handleViewClient(client: Client) {
  // TODO: Open ClientInfoModal
  console.log('View client:', client)
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex justify-start">
      <Button @click="handleAddCarToStorage" variant="outline">
        Ajouter une voiture au dépôt
      </Button>
    </div>

    <div v-if="storedCars.length === 0">Chargement...</div>
    <CarStorageTable v-else :cars="storedCars" @view-client="handleViewClient" />
  </div>
</template>
