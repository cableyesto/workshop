<script setup lang="ts">
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Button } from '@/components/ui/button'
import EmployeeTable from '../../components/EmployeeTable.vue'
import { useMechanicsQuery, useReceptionistsQuery } from '../../api/employees'

const { data: mechanics, isLoading: isLoadingMechanics } = useMechanicsQuery()
const { data: receptionists, isLoading: isLoadingReceptionists } = useReceptionistsQuery()

function handleAddEmployee() {
  // TODO: Open modal to add employee
  console.log('Add employee')
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-3xl font-bold">Employés</h1>

    <Tabs default-value="receptionists" class="w-full">
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
        />
      </TabsContent>

      <TabsContent value="mechanics">
        <div v-if="isLoadingMechanics">Chargement...</div>
        <EmployeeTable v-else-if="mechanics" :employees="mechanics" type="mechanic" />
      </TabsContent>
    </Tabs>
  </div>
</template>
