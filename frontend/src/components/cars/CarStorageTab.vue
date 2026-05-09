<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import CarStorageTable from './CarStorageTable.vue'
import CarStorageDialog from './CarStorageDialog.vue'
import ClientInfoModal from './ClientInfoModal.vue'
import { useStoredCarsQuery } from '../../api/cars'
import type { Client } from '../../types/client'

const { data: storedCars, isLoading } = useStoredCarsQuery()
const isStorageDialogOpen = ref(false)
const isClientInfoModalOpen = ref(false)
const selectedClient = ref<Client | null>(null)

function handleAddCarToStorage() {
  isStorageDialogOpen.value = true
}

function handleViewClient(client: Client) {
  selectedClient.value = client
  isClientInfoModalOpen.value = true
}

function handleCloseClientInfo() {
  isClientInfoModalOpen.value = false
  selectedClient.value = null
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex justify-start">
      <Button @click="handleAddCarToStorage" variant="outline">
        Ajouter une voiture au dépôt
      </Button>
    </div>

    <div v-if="isLoading">Chargement...</div>
    <CarStorageTable
      v-else-if="storedCars"
      :cars="storedCars"
      @view-client="handleViewClient"
    />

    <CarStorageDialog :open="isStorageDialogOpen" @close="isStorageDialogOpen = false" />

    <ClientInfoModal
      :open="isClientInfoModalOpen"
      :client="selectedClient"
      @close="handleCloseClientInfo"
    />
  </div>
</template>
