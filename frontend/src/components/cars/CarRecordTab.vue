<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import CarRecordTable from './CarRecordTable.vue'
import ClientFormDialog from './ClientFormDialog.vue'
import CarEditDialog from './CarEditDialog.vue'
import CarCreateDialog from './CarCreateDialog.vue'
import { useAllCarsQuery } from '../../api/cars'
import type { Car } from '../../types/cars'
import type { Client } from '../../types/client'

const { data: cars, isLoading } = useAllCarsQuery()

const isCreateDialogOpen = ref(false)
const isClientModalOpen = ref(false)
const isCarEditDialogOpen = ref(false)
const selectedClient = ref<Client | null>(null)
const selectedCar = ref<Car | null>(null)

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

function handleEditCar(car: Car) {
  selectedCar.value = car
  isCarEditDialogOpen.value = true
}

function handleCloseCarEditDialog() {
  isCarEditDialogOpen.value = false
  selectedCar.value = null
}

function handleCloseCreateDialog() {
  isCreateDialogOpen.value = false
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

    <CarEditDialog
      :open="isCarEditDialogOpen"
      :car="selectedCar"
      @close="handleCloseCarEditDialog"
      @success="handleCloseCarEditDialog"
    />

    <CarCreateDialog
      :open="isCreateDialogOpen"
      @close="handleCloseCreateDialog"
      @success="handleCloseCreateDialog"
    />
  </div>
</template>
