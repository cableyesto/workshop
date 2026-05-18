<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import MechanicCombobox from './MechanicCombobox.vue'
import { useMechanicsQuery } from '@/api/employees'
import { useInterventionQuery, useUpdateInterventionMutation } from '@/api/interventions'
import type { InterventionType, DocumentType, InterventionStatus } from '@/types/intervention'

interface Props {
  interventionId: number | undefined
  isEditMode: boolean
  mechanicId: number | undefined
  licensePlate: string
}

const props = withDefaults(defineProps<Props>(), {
  mechanicId: undefined,
  licensePlate: '',
})

const emit = defineEmits<{
  next: []
  back: []
  cancel: []
}>()

const hasAttemptedSubmit = ref(false)

// Status labels with emojis
const statusLabels: Record<InterventionStatus, string> = {
  Assigned: '🏷️ Affectée',
  'In Progress': '⏳ En cours',
  Paused: '⏸️ En pause',
  Stopped: '✅ Terminée',
}

// Current status (default for new interventions)
const currentStatus = ref<InterventionStatus>('Assigned')

// Fetch mechanics
const { data: mechanics, isLoading: mechanicsLoading } = useMechanicsQuery()

// Fetch intervention data (cached, only in edit mode)
const { data: interventionData } = useInterventionQuery(props.interventionId, props.isEditMode)

// Computed mechanic options for combobox
const mechanicOptions = computed(() => {
  if (!mechanics.value) return []
  return mechanics.value.map((m) => ({
    value: m.id,
    label: `${m.firstName} ${m.lastName}`,
  }))
})

// Get current date and time for defaults
const getCurrentDate = () => {
  const now = new Date()
  return now.toISOString().split('T')[0] // YYYY-MM-DD
}

const getCurrentTime = () => {
  const now = new Date()
  return now.toTimeString().slice(0, 5) // HH:mm
}

// Validation schema with all fields
const schema = toTypedSchema(
  z.object({
    interventionType: z.enum(['Repair', 'Diagnostic'], {
      required_error: "Sélectionnez le type d'intervention",
    }),
    documentType: z.enum(['Estimate', 'Invoice'], {
      required_error: 'Sélectionnez le type de document',
    }),
    mechanicId: z.number({ required_error: 'Sélectionnez un mécanicien' }),
    licensePlate: z
      .string()
      .min(1, 'La plaque est requise')
      .refine(
        (val) => val.length === 0 || val.length === 9,
        'La plaque doit contenir exactement 9 caractères',
      )
      .refine(
        (val) => val.length === 0 || /^[A-Z]{2}-\d{3}-[A-Z]{2}$/.test(val),
        'Format invalide. Attendu: AB-123-CD',
      ),
    date: z.string().min(1, 'La date est requise'),
    startTime: z.string().min(1, "L'heure est requise"),
  }),
)

const { handleSubmit, errors, defineField, setValues } = useForm({
  validationSchema: schema,
  initialValues: {
    interventionType: 'Repair',
    documentType: 'Estimate',
    mechanicId: props.mechanicId,
    licensePlate: props.licensePlate || '',
    date: getCurrentDate(),
    startTime: getCurrentTime(),
  },
  validateOnMount: false,
})

const [interventionType] = defineField('interventionType')
const [documentType] = defineField('documentType')
const [mechanicId] = defineField('mechanicId')
const [licensePlate] = defineField('licensePlate', {
  validateOnModelUpdate: false,
})
const [date] = defineField('date')
const [startTime] = defineField('startTime')

// Setup mutation
const { mutate: updateIntervention } = useUpdateInterventionMutation(
  () => {
    emit('next')
  },
  (error) => {
    console.error('Error updating intervention:', error)
    alert('Erreur: ' + error.message)
  },
)

// Watch for intervention data and populate form
watch(interventionData, (data) => {
  if (data && props.isEditMode) {
    setValues({
      interventionType: data.interventionType || 'Repair',
      documentType: data.documentType || 'Estimate',
      mechanicId: data.mechanic.id,
      licensePlate: data.car.licensePlate,
      date: data.date,
      startTime: data.startTime,
    })
    currentStatus.value = data.status
  }
}, { immediate: true })

function formatLicensePlate(event: Event) {
  hasAttemptedSubmit.value = false

  const input = event.target as HTMLInputElement
  let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '')

  if (value.length > 2 && value.length <= 5) {
    value = value.slice(0, 2) + '-' + value.slice(2)
  } else if (value.length > 5) {
    value = value.slice(0, 2) + '-' + value.slice(2, 5) + '-' + value.slice(5, 7)
  }

  licensePlate.value = value
}

function updateMechanicId(id: number | undefined) {
  mechanicId.value = id
}

const onSubmit = handleSubmit(
  async (values) => {
    if (!props.interventionId) {
      alert('Erreur: ID intervention manquant')
      return
    }

    updateIntervention({
      interventionId: props.interventionId,
      data: {
        interventionType: values.interventionType as InterventionType,
        documentType: values.documentType as DocumentType,
        mechanicId: values.mechanicId,
        licensePlate: values.licensePlate,
        date: values.date,
        startTime: values.startTime,
      },
    })
  },
  () => {
    hasAttemptedSubmit.value = true
  },
)
</script>

<template>
  <form @submit="onSubmit" class="grid gap-6">
    <h2 class="text-2xl font-semibold">Étape 2: Type d'intervention</h2>
    <!-- Breadcrumb -->
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem>
          <BreadcrumbPage>Type</BreadcrumbPage>
        </BreadcrumbItem>
        <BreadcrumbSeparator />
        <BreadcrumbItem>
          <span class="text-muted-foreground">Remarque</span>
        </BreadcrumbItem>
        <BreadcrumbSeparator />
        <BreadcrumbItem>
          <span class="text-muted-foreground">Acte</span>
        </BreadcrumbItem>
      </BreadcrumbList>
    </Breadcrumb>

    <div class="grid grid-cols-2 gap-6">
      <!-- Left Column -->
      <div class="grid gap-6">
        <!-- Intervention Type -->
        <FieldGroup class="gap-2">
          <FieldLabel>Type d'intervention</FieldLabel>
          <Field>
            <RadioGroup
              v-model="interventionType"
              default-value="Repair"
              class="flex flex-col gap-2"
            >
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="Repair" id="type-repair" />
                <label for="type-repair" class="cursor-pointer ml-2">Réparation</label>
              </div>
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="Diagnostic" id="type-diagnostic" />
                <label for="type-diagnostic" class="cursor-pointer ml-2">Diagnostic</label>
              </div>
            </RadioGroup>
            <FieldError v-if="hasAttemptedSubmit && errors.interventionType">{{
              errors.interventionType
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Document Type -->
        <FieldGroup class="gap-2">
          <FieldLabel>Type de document</FieldLabel>
          <Field>
            <RadioGroup v-model="documentType" default-value="Estimate" class="flex flex-col gap-2">
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="Estimate" id="doc-estimate" />
                <label for="doc-estimate" class="cursor-pointer ml-2">Devis</label>
              </div>
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="Invoice" id="doc-invoice" />
                <label for="doc-invoice" class="cursor-pointer ml-2">Facture</label>
              </div>
            </RadioGroup>
            <FieldError v-if="hasAttemptedSubmit && errors.documentType">{{
              errors.documentType
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Mechanic -->
        <FieldGroup class="gap-2">
          <FieldLabel>Mécanicien</FieldLabel>
          <Field>
            <MechanicCombobox
              :model-value="mechanicId"
              :options="mechanicOptions"
              :loading="mechanicsLoading"
              @update:model-value="updateMechanicId"
            />
            <FieldError v-if="hasAttemptedSubmit && errors.mechanicId">{{
              errors.mechanicId
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Date -->
        <FieldGroup class="gap-2">
          <FieldLabel for="date">Date</FieldLabel>
          <Field>
            <Input id="date" v-model="date" type="date" class="w-full" />
            <FieldError v-if="hasAttemptedSubmit && errors.date">{{ errors.date }}</FieldError>
          </Field>
        </FieldGroup>
      </div>

      <!-- Right Column -->
      <div class="flex flex-col gap-6">
        <!-- Status (Display Only) -->
        <div>
          <div class="text-sm font-medium text-muted-foreground mb-2">Statut</div>
          <div class="text-sm font-medium">
            {{ statusLabels[currentStatus] }}
          </div>
        </div>

        <!-- Spacer to align with left column -->
        <div class="h-30"></div>

        <!-- License Plate -->
        <FieldGroup class="gap-2">
          <FieldLabel for="licensePlate">Immatriculation voiture</FieldLabel>
          <Field>
            <Input
              id="licensePlate"
              :model-value="licensePlate"
              type="text"
              placeholder="AB-123-CD"
              maxlength="9"
              class="w-full"
              @input="formatLicensePlate"
            />
            <FieldError v-if="hasAttemptedSubmit && errors.licensePlate">{{
              errors.licensePlate
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Start Time -->
        <FieldGroup class="gap-2">
          <FieldLabel for="startTime">Heure départ</FieldLabel>
          <Field>
            <Input id="startTime" v-model="startTime" type="time" class="w-full" />
            <FieldError v-if="hasAttemptedSubmit && errors.startTime">{{
              errors.startTime
            }}</FieldError>
          </Field>
        </FieldGroup>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-2">
      <Button type="button" variant="outline" @click="$emit('back')">Retour</Button>
      <Button type="submit">Suivant</Button>
      <Button type="button" variant="destructive" @click="$emit('cancel')">Annuler</Button>
    </div>
  </form>
</template>
