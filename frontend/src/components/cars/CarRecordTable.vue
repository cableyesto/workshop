<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import type { Car } from '../../types/cars'
import type { Client } from '../../types/client'

interface Props {
  cars: Car[]
}

defineProps<Props>()

const emit = defineEmits<{
  viewClient: [client: Client]
  editCar: [car: Car]
  addToStorage: [licensePlate: string]
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
          <TableHead>Modification</TableHead>
          <TableHead>Ajout au dépôt</TableHead>
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
            <button
              class="text-blue-600 hover:underline cursor-pointer"
              @click="emit('editCar', car)"
            >
              Actualiser
            </button>
          </TableCell>
          <TableCell>
            <button
              v-if="!car.isStored"
              class="text-blue-600 hover:underline cursor-pointer"
              @click="emit('addToStorage', car.licensePlate)"
            >
              Ajouter
            </button>
            <span v-else class="text-gray-400">Au dépôt</span>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
