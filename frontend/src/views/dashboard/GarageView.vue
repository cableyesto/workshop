<script setup lang="ts">
import { computed } from 'vue'
import { useGarageQuery } from '@/api/garage'
import type { TimeSlot } from '@/types'

const token = localStorage.getItem('jwt_token')
const tokenPart = token?.split('.')[1]
const payload = tokenPart ? JSON.parse(atob(tokenPart)) : null
const garageId = payload?.garage_id

const { data: garage, isLoading, error } = useGarageQuery(garageId)

const dayNames = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']

const scheduleByDay = computed(() => {
  if (!garage.value) return []

  const grouped: Record<string, TimeSlot[]> = {}

  garage.value.timeSlots.forEach((slot) => {
    ;(grouped[slot.dayOfWeek] ??= []).push(slot)
  })

  return dayNames.map((name) => {
    const slots = grouped[name] || []
    return {
      name,
      display: formatDaySlots(slots),
    }
  })
})

function formatDaySlots(slots: TimeSlot[]): string {
  if (slots.length === 0) return 'Fermé'

  if (slots.length === 1) {
    const slot = slots[0]
    // Check if slot spans across lunch (needs splitting)
    if (needsSplitting(slot!)) {
      return `${formatTime(slot!.startTime)} - 12h00 / 13h00 - ${formatTime(slot!.endTime)}`
    }
    return `${formatTime(slot!.startTime)} - ${formatTime(slot!.endTime)}`
  }

  return slots.map((s) => `${formatTime(s.startTime)} - ${formatTime(s.endTime)}`).join(' / ')
}

function needsSplitting(slot: TimeSlot): boolean {
  if (!slot) return false
  const startHour = parseInt(slot.startTime.split(':')[0]!, 10)
  const endHour = parseInt(slot.endTime.split(':')[0]!, 10)
  return startHour < 13 && endHour > 13
}

function formatTime(time: string): string {
  return time.replace(':', 'h')
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <div v-if="isLoading" class="text-gray-600">Chargement...</div>
    <div v-else-if="error" class="text-red-600">Erreur lors du chargement des données</div>
    <div v-else-if="garage" class="flex flex-col gap-6">
      <h1 class="text-3xl font-bold">Bienvenue chez {{ garage.name }}</h1>
      <div class="flex flex-col gap-4">
        <h2 class="text-xl font-semibold">Horaires d'ouverture :</h2>
        <div class="flex flex-col gap-2">
          <div v-for="day in scheduleByDay" :key="day.name" class="flex gap-4">
            <span class="w-24 font-medium">{{ day.name }} :</span>
            <span class="text-gray-700">{{ day.display }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
