<script setup lang="ts">
import { ref, computed } from 'vue'
import { useMutation, useQueryCache } from '@pinia/colada'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Button } from '@/components/ui/button'
import EmployeeTable from '../../components/EmployeeTable.vue'
import EmployeeDialog from '../../components/EmployeeDialog.vue'
import {
  useMechanicsQuery,
  useReceptionistsQuery,
  createMechanicAPI,
  createReceptionistAPI,
} from '../../api/employees'
import type { EmployeeFormData, Mechanic, Receptionist } from '../../types/employee'

const activeTab = ref<'receptionists' | 'mechanics'>('receptionists')
const isDialogOpen = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const selectedEmployee = ref<Mechanic | Receptionist | null>(null)

// Convert plural tab name to singular for dialog type
const dialogType = computed<'mechanic' | 'receptionist'>(() => {
  return activeTab.value === 'mechanics' ? 'mechanic' : 'receptionist'
})

const queryCache = useQueryCache()

const { data: mechanics, isLoading: isLoadingMechanics } = useMechanicsQuery()
const { data: receptionists, isLoading: isLoadingReceptionists } = useReceptionistsQuery()

// Mechanic mutation
const { mutate: createMechanic, isLoading: isCreatingMechanic } = useMutation({
  key: ['create-mechanic'],
  mutation: createMechanicAPI,
  onSuccess: () => {
    isDialogOpen.value = false
    queryCache.invalidateQueries({
      key: ['mechanics'],
      exact: true,
    })
  },
  onError: (error) => {
    console.error('Error creating mechanic:', error)
    //TODO improve error displayed
    alert(`Erreur: ${error.message}`)
  },
})

// Receptionist mutation
const { mutate: createReceptionist, isLoading: isCreatingReceptionist } = useMutation({
  key: ['create-receptionist'],
  mutation: createReceptionistAPI,
  onSuccess: () => {
    isDialogOpen.value = false
    queryCache.invalidateQueries({
      key: ['receptionists'],
      exact: true,
    })
  },
  onError: (error) => {
    console.error('Error creating receptionist:', error)
    //TODO improve error displayed
    alert(`Erreur: ${error.message}`)
  },
})

function handleAddEmployee() {
  dialogMode.value = 'create'
  selectedEmployee.value = null
  isDialogOpen.value = true
}

function handleUpdateEmployee(employee: Mechanic | Receptionist) {
  dialogMode.value = 'edit'
  selectedEmployee.value = employee
  isDialogOpen.value = true
}

function handleCloseDialog() {
  isDialogOpen.value = false
  dialogMode.value = 'create'
  selectedEmployee.value = null
}

function handleSubmit(data: EmployeeFormData) {
  if (dialogMode.value === 'edit') {
    // TODO: Implement update mutations
    console.log('Update employee:', { id: selectedEmployee.value?.id, data })
    alert('Update functionality coming soon!')
  } else {
    // Create mode
    if (data.type === 'mechanic') {
      createMechanic(data)
    } else {
      createReceptionist(data)
    }
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-3xl font-bold">Employés</h1>

    <Tabs v-model="activeTab" default-value="receptionists" class="w-full">
      <div class="flex items-center justify-between mb-4">
        <TabsList>
          <TabsTrigger value="receptionists">Accueil</TabsTrigger>
          <TabsTrigger value="mechanics">Mécanicien</TabsTrigger>
        </TabsList>

        <Button @click="handleAddEmployee" variant="outline">Ajouter à l'équipe</Button>
      </div>

      <TabsContent value="receptionists">
        <div v-if="isLoadingReceptionists">Chargement...</div>
        <EmployeeTable
          v-else-if="receptionists"
          :employees="receptionists"
          type="receptionist"
          @update="handleUpdateEmployee"
        />
      </TabsContent>

      <TabsContent value="mechanics">
        <div v-if="isLoadingMechanics">Chargement...</div>
        <EmployeeTable
          v-else-if="mechanics"
          :employees="mechanics"
          type="mechanic"
          @update="handleUpdateEmployee"
        />
      </TabsContent>
    </Tabs>

    <EmployeeDialog
      :open="isDialogOpen"
      :type="dialogType"
      :mode="dialogMode"
      :employee="selectedEmployee"
      @close="handleCloseDialog"
      @submit="handleSubmit"
    />
  </div>
</template>
