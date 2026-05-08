<script setup lang="ts">
import { ref, computed } from 'vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Button } from '@/components/ui/button'
import EmployeeTable from '../../components/EmployeeTable.vue'
import EmployeeDialog from '../../components/EmployeeDialog.vue'
import {
  useMechanicsQuery,
  useReceptionistsQuery,
  useCreateMechanicMutation,
  useUpdateMechanicMutation,
  useDeleteMechanicMutation,
  useCreateReceptionistMutation,
  useUpdateReceptionistMutation,
  useDeleteReceptionistMutation,
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

const { data: mechanics, isLoading: isLoadingMechanics } = useMechanicsQuery()
const { data: receptionists, isLoading: isLoadingReceptionists } = useReceptionistsQuery()

// Mechanic mutations
const { mutate: createMechanic } = useCreateMechanicMutation(isDialogOpen)
const { mutate: updateMechanic } = useUpdateMechanicMutation(isDialogOpen)
const { mutate: deleteMechanic } = useDeleteMechanicMutation(isDialogOpen)

// Receptionist mutations
const { mutate: createReceptionist } = useCreateReceptionistMutation(isDialogOpen)
const { mutate: updateReceptionist } = useUpdateReceptionistMutation(isDialogOpen)
const { mutate: deleteReceptionist } = useDeleteReceptionistMutation(isDialogOpen)

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

function handleDeleteEmployee() {
  if (!selectedEmployee.value) return

  // if (!confirm('Êtes-vous sûr de vouloir supprimer cet employé ?')) {
  //   return
  // }

  if (dialogType.value === 'mechanic') {
    deleteMechanic(selectedEmployee.value.id)
  } else {
    deleteReceptionist(selectedEmployee.value.id)
  }
}

function handleSubmit(data: EmployeeFormData) {
  if (dialogMode.value === 'edit' && selectedEmployee.value) {
    // Update mode
    if (data.type === 'mechanic') {
      updateMechanic({ id: selectedEmployee.value.id, data })
    } else {
      updateReceptionist({ id: selectedEmployee.value.id, data })
    }
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
      @delete="handleDeleteEmployee"
    />
  </div>
</template>
