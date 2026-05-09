<script setup lang="ts">
import { ref } from 'vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import CarRecordTab from '../../components/cars/CarRecordTab.vue'
import CarStorageTab from '../../components/cars/CarStorageTab.vue'
import CarInterventionTab from '../../components/cars/CarInterventionTab.vue'
import CarRestitutionTab from '../../components/cars/CarRestitutionTab.vue'

const activeTab = ref<'fiches' | 'depot' | 'intervention' | 'restitution'>('depot')
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-3xl font-bold">Voitures</h1>

    <Tabs v-model="activeTab" default-value="depot" class="w-full">
      <TabsList>
        <TabsTrigger value="fiches">Fiches</TabsTrigger>
        <TabsTrigger value="depot">Dépôt</TabsTrigger>
        <TabsTrigger value="intervention">Intervention</TabsTrigger>
        <TabsTrigger value="restitution">Restitution</TabsTrigger>
      </TabsList>

      <TabsContent value="fiches">
        <CarRecordTab />
      </TabsContent>

      <TabsContent value="depot">
        <CarStorageTab />
      </TabsContent>

      <TabsContent value="intervention">
        <CarInterventionTab />
      </TabsContent>

      <TabsContent value="restitution">
        <CarRestitutionTab />
      </TabsContent>
    </Tabs>
  </div>
</template>
