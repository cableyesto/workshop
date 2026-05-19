<script setup lang="ts">
import { ref, defineAsyncComponent } from 'vue'
import InterventionSearchResult from '@/components/interventions/InterventionSearchResult.vue'
import { searchInterventionAPI, createInterventionAPI } from '@/api/interventions'
import type { SearchInterventionResponse } from '@/types/intervention'

// Lazy load step components
const InterventionStep1 = defineAsyncComponent(
  () => import('@/components/interventions/InterventionStep1.vue'),
)
const InterventionStep2 = defineAsyncComponent(
  () => import('@/components/interventions/InterventionStep2.vue'),
)
const InterventionStep3 = defineAsyncComponent(
  () => import('@/components/interventions/InterventionStep3.vue'),
)
const InterventionStep4 = defineAsyncComponent(
  () => import('@/components/interventions/InterventionStep4.vue'),
)

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
    // API not cached fetch
    searchResult.value = await searchInterventionAPI(mechanicId.value, licensePlate.value)
    showSearchResult.value = true
  } catch (error) {
    console.error('Search error:', error)
  }
}

async function handleCreate() {
  showSearchResult.value = false

  if (!mechanicId.value || !licensePlate.value) {
    return
  }

  try {
    const response = await createInterventionAPI(mechanicId.value, licensePlate.value)

    interventionId.value = response.id
    isEditMode.value = false
    currentStep.value = 2
  } catch (error) {
    console.error('Create error:', error)
  }
}

function handleEdit(id: number) {
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
  currentStep.value = 3
}

function handleStep3Complete() {
  currentStep.value = 4
}

function handleStep4Complete() {
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

    <!-- Step 3: Remarks -->
    <div v-if="currentStep === 3" class="flex justify-center">
      <div class="w-full max-w-4xl">
        <InterventionStep3
          :intervention-id="interventionId"
          :is-edit-mode="isEditMode"
          @next="handleStep3Complete"
          @back="handleStepBack"
          @cancel="handleCancelWizard"
        />
      </div>
    </div>

    <!-- Step 4: Acts/Tasks -->
    <div v-if="currentStep === 4" class="flex justify-center">
      <div class="w-full max-w-4xl">
        <InterventionStep4
          :intervention-id="interventionId"
          :is-edit-mode="isEditMode"
          @next="handleStep4Complete"
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
