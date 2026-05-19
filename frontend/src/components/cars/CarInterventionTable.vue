<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import type { CarWithIntervention } from '../../types/cars'
import type { Client } from '../../types/client'

interface Props {
  cars: CarWithIntervention[]
}

defineProps<Props>()

const emit = defineEmits<{
  viewClient: [client: Client]
}>()

const statusLabels: Record<string, string> = {
  Active: '⏳ En cours',
  Paused: '⏸️ En pause',
  Assigned: '🏷️ Affectée',
  Completed: '✅ Terminée',
}
</script>

<template>
  <!-- <div class="rounded-md border"> -->
  <div>
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Marque</TableHead>
          <TableHead>Modèle</TableHead>
          <TableHead>Couleur</TableHead>
          <TableHead>Plaque</TableHead>
          <TableHead>Statut</TableHead>
          <TableHead>Fiche client</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="car in cars" :key="car.id">
          <TableCell>{{ car.manufacturer }}</TableCell>
          <TableCell>{{ car.model }}</TableCell>
          <TableCell>{{ car.color }}</TableCell>
          <TableCell>{{ car.licensePlate }}</TableCell>
          <TableCell>{{ statusLabels[car.intervention.globalStatus] }}</TableCell>
          <TableCell>
            <button
              class="text-blue-600 hover:underline cursor-pointer"
              @click="emit('viewClient', car.client)"
            >
              {{ car.client.lastName }}
              {{ car.client.firstName }}
            </button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
