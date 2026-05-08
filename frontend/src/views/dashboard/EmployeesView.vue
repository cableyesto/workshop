<script setup lang="ts">
import { ref, computed } from 'vue'
import { useMutation, useQueryCache } from '@pinia/colada'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Button } from '@/components/ui/button'
import EmployeeTable from '../../components/EmployeeTable.vue'
import EmployeeDialog from '../../components/EmployeeDialog.vue'
import { useMechanicsQuery, useReceptionistsQuery, createMechanicAPI } from '../../api/employees'
import { getToken } from '../../utils/auth'
import type { EmployeeFormData } from '../../types/employee'

const activeTab = ref<'receptionists' | 'mechanics'>('receptionists')
const isDialogOpen = ref(false)

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
  onSettled: () => {
    queryCache.invalidateQueries({
      key: ['mechanics'],
      exact: true,
    })
  },
})

// Receptionist mutation
const {
  mutate: createReceptionist,
  status,
  asyncStatus,
} = useMutation({
  key: ['create-receptionist'],
  mutation: async (data: EmployeeFormData) => {
    const response = await fetch('/api/receptionists', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${getToken()}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })

    if (!response.ok) {
      throw new Error('Failed to create receptionist')
    }

    return response.json()
  },
  onSettled: () => {
    queryCache.invalidateQueries({
      key: ['receptionists'],
      exact: true,
    })
  },
})

function handleAddEmployee() {
  isDialogOpen.value = true
}

function handleCloseDialog() {
  isDialogOpen.value = false
}

function handleSubmit(data: EmployeeFormData) {
  if (data.type === 'mechanic') {
    createMechanic(data)
  } else {
    createReceptionist(data)
    console.log('Receptionist mutation called:', {
      status: status.value,
      asyncStatus: asyncStatus.value,
    })
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
        <EmployeeTable v-else-if="receptionists" :employees="receptionists" type="receptionist" />
      </TabsContent>

      <TabsContent value="mechanics">
        <div v-if="isLoadingMechanics">Chargement...</div>
        <EmployeeTable v-else-if="mechanics" :employees="mechanics" type="mechanic" />
      </TabsContent>
    </Tabs>

    <EmployeeDialog
      :open="isDialogOpen"
      :type="dialogType"
      @close="handleCloseDialog"
      @submit="handleSubmit"
    />
  </div>
</template>
