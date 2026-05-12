<script setup lang="ts">
import { ref } from 'vue'
import InterventionStep1 from '@/components/interventions/InterventionStep1.vue'

const mechanicId = ref<number | undefined>(undefined)
const licensePlate = ref('')

function handleSearch() {
  console.log('Search:', { mechanicId: mechanicId.value, licensePlate: licensePlate.value })
  // TODO: Call search API
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-3xl font-bold">Interventions</h1>

    <div class="flex justify-center">
      <div class="w-full max-w-md">
        <InterventionStep1
          v-model:mechanic-id="mechanicId"
          v-model:license-plate="licensePlate"
          @search="handleSearch"
        />
      </div>
    </div>
  </div>
</template>
