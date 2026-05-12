<script setup lang="ts">
import { ref } from 'vue'
import InterventionStep1 from '@/components/interventions/InterventionStep1.vue'
import InterventionSearchResult from '@/components/interventions/InterventionSearchResult.vue'
import { useSearchInterventionQuery } from '@/api/interventions'
import type { SearchInterventionResponse } from '@/types/intervention'

const mechanicId = ref<number | undefined>(undefined)
const licensePlate = ref('')
const showSearchResult = ref(false)
const searchResult = ref<SearchInterventionResponse | null>(null)

const searchQuery = useSearchInterventionQuery(mechanicId.value || 0, licensePlate.value)

async function handleSearch() {
  if (!mechanicId.value || !licensePlate.value) return

  try {
    // Manually trigger the query
    const result = await searchQuery.refetch()

    if (result.data) {
      searchResult.value = result.data
      showSearchResult.value = true
    }
  } catch (error) {
    console.error('Search error:', error)
  }
}

function handleCreate() {
  console.log('Create new intervention')
  showSearchResult.value = false
  // TODO: Navigate to Step 2 or create intervention
}

function handleEdit(interventionId: number) {
  console.log('Edit intervention:', interventionId)
  showSearchResult.value = false
  // TODO: Load intervention and navigate to wizard
}

function handleCancel() {
  showSearchResult.value = false
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

    <!-- Search Result Dialog -->
    <InterventionSearchResult
      :open="showSearchResult"
      :result="searchResult"
      @create="handleCreate"
      @edit="handleEdit"
      @cancel="handleCancel"
    />
  </div>
</template>
