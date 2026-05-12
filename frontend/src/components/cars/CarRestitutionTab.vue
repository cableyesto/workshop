<script setup lang="ts">
import { ref } from 'vue'
import { useQueryCache } from '@pinia/colada'
import { RefreshCw } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import CarRestitutionTable from './CarRestitutionTable.vue'
import ClientInfoModal from './ClientInfoModal.vue'
import {
  useRestitutionCarsQuery,
  usePatchClientCalledBackMutation,
  useRemoveCarFromStorageMutation,
} from '../../api/cars'
import type { Client } from '../../types/client'

const { data: restitutionCars, isLoading } = useRestitutionCarsQuery()

const isClientInfoModalOpen = ref(false)
const selectedClient = ref<Client | null>(null)
const updatingClientIds = ref<Set<number>>(new Set())

const queryCache = useQueryCache()

const { mutate: patchClientCalledBack } = usePatchClientCalledBackMutation(updatingClientIds)
const { mutate: removeCarFromStorage } = useRemoveCarFromStorageMutation()

function handleRefresh() {
  queryCache.invalidateQueries({
    key: ['cars', 'restitution'],
    exact: true,
  })
}

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
  // if (confirm('Retirer cette voiture du dépôt ?')) {
  //   removeCarFromStorage(carId)
  // }
  removeCarFromStorage(carId)
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex justify-start">
      <Button @click="handleRefresh" variant="outline">
        <RefreshCw class="h-4 w-4" /> Rafraîchir
      </Button>
    </div>
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
