<script setup lang="ts">
import { ref } from 'vue'
import InterventionStep1 from '@/components/interventions/InterventionStep1.vue'
import InterventionStep2 from '@/components/interventions/InterventionStep2.vue'
import InterventionSearchResult from '@/components/interventions/InterventionSearchResult.vue'
import { apiRequest } from '@/api/helpers'
import type { SearchInterventionResponse } from '@/types/intervention'

// Wizard state
const currentStep = ref(1)
const interventionId = ref<number | undefined>(undefined)
const isEditMode = ref(false)

// Step 1 data
const mechanicId = ref<number | undefined>(undefined)
const licensePlate = ref('')
const showSearchResult = ref(false)
const searchResult = ref<SearchInterventionResponse | null>(null)

async function handleSearch() {
  if (!mechanicId.value || !licensePlate.value) {
    return
  }

  try {
    const result = await apiRequest<SearchInterventionResponse>(
      `/api/interventions/search?mechanicId=${mechanicId.value}&licensePlate=${licensePlate.value}`,
      {},
      'Failed to search intervention',
    )

    searchResult.value = result
    showSearchResult.value = true
  } catch (error) {
    console.error('Create error:', error)
  }
}

async function handleCreate() {
  showSearchResult.value = false

  if (!mechanicId.value || !licensePlate.value) {
    return
  }

  try {
    const response = await apiRequest<{ id: number }>(
      '/api/interventions',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          mechanicId: mechanicId.value,
          licensePlate: licensePlate.value,
        }),
      },
      'Failed to create intervention',
    )

    interventionId.value = response.id
    isEditMode.value = false
    currentStep.value = 2
  } catch (error) {
    console.error('Create error:', error)
  }
}

function handleEdit(id: number) {
  console.log('Editing intervention:', id)
  showSearchResult.value = false

  interventionId.value = id
  isEditMode.value = true
  currentStep.value = 2
}

function handleCancelSearch() {
  showSearchResult.value = false
}

function handleStepBack() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

function handleStep2Complete() {
  // TODO: Move to Step 3 when ready
  console.log('Step 2 complete')
  resetWizard()
}

function handleCancelWizard() {
  resetWizard()
}

function resetWizard() {
  currentStep.value = 1
  interventionId.value = undefined
  isEditMode.value = false
  mechanicId.value = undefined
  licensePlate.value = ''
  searchResult.value = null
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h1 class="text-3xl font-bold">Interventions</h1>

    <!-- Step 1: Search -->
    <div v-if="currentStep === 1" class="flex justify-center">
      <div class="w-full max-w-md">
        <InterventionStep1
          v-model:mechanic-id="mechanicId"
          v-model:license-plate="licensePlate"
          @search="handleSearch"
        />
      </div>
    </div>

    <!-- Step 2: Type Selection -->
    <div v-if="currentStep === 2" class="flex justify-center">
      <div class="w-full max-w-4xl">
        <InterventionStep2
          :intervention-id="interventionId"
          :is-edit-mode="isEditMode"
          :mechanic-id="mechanicId"
          :license-plate="licensePlate"
          @next="handleStep2Complete"
          @back="handleStepBack"
          @cancel="handleCancelWizard"
        />
      </div>
    </div>

    <!-- Search Result Dialog -->
    <InterventionSearchResult
      :open="showSearchResult"
      :result="searchResult"
      @create="handleCreate"
      @edit="handleEdit"
      @cancel="handleCancelSearch"
    />
  </div>
</template>
