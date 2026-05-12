<script setup lang="ts">
import { ref } from 'vue'
import InterventionStep1 from '@/components/interventions/InterventionStep1.vue'

const mechanicId = ref<number | null>(null)
const licensePlate = ref('')

function handleSearch() {
  console.log('Search:', { mechanicId: mechanicId.value, licensePlate: licensePlate.value })
  // TODO: Call search API
}
</script>

<template>
  <div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Interventions</h1>

    <div class="max-w-md">
      <InterventionStep1
        v-model:mechanic-id="mechanicId"
        v-model:license-plate="licensePlate"
        @search="handleSearch"
      />
    </div>

    <!-- Debug info -->
    <div class="mt-6 p-4 bg-gray-100 rounded">
      <h2 class="font-semibold mb-2">Debug:</h2>
      <pre>{{ { mechanicId, licensePlate } }}</pre>
    </div>
  </div>
</template>
