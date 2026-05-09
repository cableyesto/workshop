<script setup lang="ts">
import { ref } from 'vue'
import { useQueryCache } from '@pinia/colada'
import { RefreshCw } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import CarInterventionTable from './CarInterventionTable.vue'
import ClientInfoModal from './ClientInfoModal.vue'
import { useInterventionCarsQuery } from '../../api/cars'
import type { Client } from '../../types/cars'

const { data: interventionCars, isLoading } = useInterventionCarsQuery()
const queryCache = useQueryCache()
const isClientInfoModalOpen = ref(false)
const selectedClient = ref<Client | null>(null)

function handleRefresh() {
  queryCache.invalidateQueries({
    key: ['cars', 'interventions'],
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
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex justify-start">
      <Button @click="handleRefresh" variant="outline">
        <RefreshCw class="h-4 w-4" /> Rafraîchir
      </Button>
    </div>

    <div v-if="isLoading">Chargement...</div>
    <CarInterventionTable
      v-else-if="interventionCars"
      :cars="interventionCars"
      @view-client="handleViewClient"
    />

    <ClientInfoModal
      :open="isClientInfoModalOpen"
      :client="selectedClient"
      @close="handleCloseClientInfo"
    />
  </div>
</template>
