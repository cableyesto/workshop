<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { useUpdateInterventionMutation } from '@/api/interventions'
import type { InterventionType, DocumentType } from '@/types/intervention'

interface Props {
  interventionId: number | undefined
  isEditMode: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  next: []
  back: []
  cancel: []
}>()

// Validation schema with English backend values
const schema = toTypedSchema(
  z.object({
    interventionType: z.enum(['Repair', 'Diagnostic'], {
      required_error: "Sélectionnez le type d'intervention",
    }),
    documentType: z.enum(['Estimate', 'Invoice'], {
      required_error: 'Sélectionnez le type de document',
    }),
  }),
)

const { handleSubmit, errors, defineField, setValues } = useForm({
  validationSchema: schema,
  initialValues: {
    interventionType: 'Repair',
    documentType: 'Estimate',
  },
})

const [interventionType] = defineField('interventionType')
const [documentType] = defineField('documentType')

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

// Load existing data in edit mode
onMounted(async () => {
  if (props.isEditMode && props.interventionId) {
    // TODO: Load intervention data from API
    // const data = await apiRequest<Intervention>(`/api/interventions/${props.interventionId}`)
    // setValues({
    //   interventionType: data.interventionType,
    //   documentType: data.documentType,
    // })
  }
})

const onSubmit = handleSubmit(async (values) => {
  if (!props.interventionId) {
    alert('Erreur: ID intervention manquant')
    return
  }

  updateIntervention({
    interventionId: props.interventionId,
    data: {
      interventionType: values.interventionType as InterventionType,
      documentType: values.documentType as DocumentType,
    },
  })
})
</script>

<template>
  <form @submit="onSubmit" class="grid gap-6">
    <h2 class="text-2xl font-semibold">Étape 2: Type d'intervention</h2>

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
        <FieldError v-if="errors.interventionType">{{ errors.interventionType }}</FieldError>
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
        <FieldError v-if="errors.documentType">{{ errors.documentType }}</FieldError>
      </Field>
    </FieldGroup>

    <!-- Actions -->
    <div class="flex gap-2">
      <Button type="button" variant="outline" @click="$emit('back')">Retour</Button>
      <Button type="submit">Suivant</Button>
      <Button type="button" variant="destructive" @click="$emit('cancel')">Annuler</Button>
    </div>
  </form>
</template>
