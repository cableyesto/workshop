<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import type { CarWithIntervention } from '../../types/cars'
import type { Client } from '../../types/client'

interface Props {
  cars: CarWithIntervention[]
  updatingClientIds: Set<number>
}

defineProps<Props>()

const emit = defineEmits<{
  viewClient: [client: Client]
  toggleCalledBack: [clientId: number, currentValue: boolean]
  removeFromStorage: [carId: number]
}>()
</script>

<template>
  <div>
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Marque</TableHead>
          <TableHead>Modèle</TableHead>
          <TableHead>Couleur</TableHead>
          <TableHead>Plaque</TableHead>
          <TableHead>Fiche client</TableHead>
          <TableHead>Client rappelé</TableHead>
          <TableHead>Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="car in cars" :key="car.id">
          <TableCell>{{ car.manufacturer }}</TableCell>
          <TableCell>{{ car.model }}</TableCell>
          <TableCell>{{ car.color }}</TableCell>
          <TableCell>{{ car.licensePlate }}</TableCell>
          <TableCell>
            <button
              class="text-blue-600 hover:underline cursor-pointer"
              @click="emit('viewClient', car.client)"
            >
              {{ car.client.lastName }} {{ car.client.firstName }}
            </button>
          </TableCell>
          <TableCell>
            <Checkbox
              :model-value="car.client.isClientCalledBack"
              :disabled="updatingClientIds.has(car.client.id) || car.client.isClientCalledBack"
              @update:model-value="() => emit('toggleCalledBack', car.client.id, car.client.isClientCalledBack)"
            />
          </TableCell>
          <TableCell>
            <button
              class="text-red-600 hover:underline cursor-pointer font-medium"
              @click="emit('removeFromStorage', car.id)"
            >
              Retirer
            </button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
