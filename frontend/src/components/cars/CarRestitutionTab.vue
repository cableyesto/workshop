<script setup lang="ts">
import { ref } from 'vue'
import CarRestitutionTable from './CarRestitutionTable.vue'
import ClientInfoModal from './ClientInfoModal.vue'
import {
  useRestitutionCarsQuery,
  usePatchClientCalledBackMutation,
  useRemoveCarFromStorageMutation,
} from '../../api/cars'
import type { Client } from '../../types/cars'

const { data: restitutionCars, isLoading } = useRestitutionCarsQuery()

const isClientInfoModalOpen = ref(false)
const selectedClient = ref<Client | null>(null)
const updatingClientIds = ref<Set<number>>(new Set())

const { mutate: patchClientCalledBack } = usePatchClientCalledBackMutation(updatingClientIds)
const { mutate: removeCarFromStorage } = useRemoveCarFromStorageMutation()

function handleViewClient(client: Client) {
  selectedClient.value = client
  isClientInfoModalOpen.value = true
}

function handleCloseClientInfo() {
  isClientInfoModalOpen.value = false
  selectedClient.value = null
}

function handleToggleCalledBack(clientId: number, currentValue: boolean) {
  patchClientCalledBack({ clientId, value: !currentValue })
}

function handleRemoveFromStorage(carId: number) {
  if (confirm('Retirer cette voiture du dépôt ?')) {
    removeCarFromStorage(carId)
  }
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div v-if="isLoading">Chargement...</div>
    <CarRestitutionTable
      v-else-if="restitutionCars"
      :cars="restitutionCars"
      :updating-client-ids="updatingClientIds"
      @view-client="handleViewClient"
      @toggle-called-back="handleToggleCalledBack"
      @remove-from-storage="handleRemoveFromStorage"
    />

    <ClientInfoModal
      :open="isClientInfoModalOpen"
      :client="selectedClient"
      @close="handleCloseClientInfo"
    />
  </div>
</template>
