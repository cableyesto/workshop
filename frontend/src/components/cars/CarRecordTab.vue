<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import CarRecordTable from './CarRecordTable.vue'
import ClientFormDialog from './ClientFormDialog.vue'
import { useAllCarsQuery } from '../../api/cars'
import type { Client, StoredCar } from '../../types/cars'

const { data: cars, isLoading } = useAllCarsQuery()

const isCreateDialogOpen = ref(false)
const isClientModalOpen = ref(false)
const isCarEditDialogOpen = ref(false)
const selectedClient = ref<Client | null>(null)
const selectedCar = ref<StoredCar | null>(null)

function handleCreateCar() {
  isCreateDialogOpen.value = true
}

function handleViewClient(client: Client) {
  selectedClient.value = client
  isClientModalOpen.value = true
}

function handleCloseClientModal() {
  isClientModalOpen.value = false
  selectedClient.value = null
}

function handleEditCar(car: StoredCar) {
  selectedCar.value = car
  isCarEditDialogOpen.value = true
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex justify-start">
      <Button @click="handleCreateCar" variant="outline">
        Créer une fiche voiture
      </Button>
    </div>

    <div v-if="isLoading">Chargement...</div>
    <CarRecordTable
      v-else-if="cars"
      :cars="cars"
      @view-client="handleViewClient"
      @edit-car="handleEditCar"
    />

    <ClientFormDialog
      :open="isClientModalOpen"
      :client="selectedClient"
      @close="handleCloseClientModal"
      @success="handleCloseClientModal"
    />

    <!-- TODO: CarCreateDialog -->
    <!-- TODO: CarEditDialog -->
  </div>
</template>
