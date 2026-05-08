<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { formatDate } from '../utils/date'
import type { Mechanic, Receptionist } from '../types/employee'

interface Props {
  employees: Mechanic[] | Receptionist[]
  type: 'mechanic' | 'receptionist'
}

defineProps<Props>()

function handleUpdate(id: number) {
  // TODO: Open modal to update employee
  console.log('Update employee', id)
}
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>Nom</TableHead>
        <TableHead>Prénom</TableHead>
        <TableHead>Date de naissance</TableHead>
        <TableHead>Date de démarrage</TableHead>
        <TableHead>Modification</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="employee in employees" :key="employee.id">
        <TableCell>{{ employee.lastName }}</TableCell>
        <TableCell>{{ employee.firstName }}</TableCell>
        <TableCell>{{ formatDate(employee.birthDate) }}</TableCell>
        <TableCell>{{ formatDate(employee.hireDate) }}</TableCell>
        <TableCell>
          <button
            @click="handleUpdate(employee.id)"
            class="text-blue-600 hover:underline focus:outline-none"
          >
            Actualiser
          </button>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
