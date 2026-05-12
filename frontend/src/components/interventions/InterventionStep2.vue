<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'

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

// Validation schema
const schema = toTypedSchema(
  z.object({
    interventionType: z.enum(['Réparation', 'Diagnostic'], {
      required_error: "Sélectionnez le type d'intervention",
    }),
    documentType: z.enum(['Devis', 'Facture'], {
      required_error: 'Sélectionnez le type de document',
    }),
  }),
)

const { handleSubmit, errors, defineField, setValues } = useForm({
  validationSchema: schema,
  initialValues: {
    interventionType: undefined,
    documentType: undefined,
  },
})

const [interventionType] = defineField('interventionType')
const [documentType] = defineField('documentType')

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
  try {
    // TODO: PATCH /api/interventions/{interventionId} with type data
    // await apiRequest(
    //   `/api/interventions/${props.interventionId}`,
    //   {
    //     method: 'PATCH',
    //     body: JSON.stringify({
    //       interventionType: values.interventionType,
    //       documentType: values.documentType,
    //     }),
    //   },
    //   'Failed to update intervention',
    // )

    emit('next')
  } catch (error) {
    console.error('Error handling submit:', error)
  }
})
</script>

<template>
  <form @submit="onSubmit" class="grid gap-6">
    <h2 class="text-2xl font-semibold">Étape 2: Type d'intervention</h2>

    <!-- Intervention Type -->
    <FieldGroup class="gap-2">
      <FieldLabel>Type d'intervention</FieldLabel>
      <Field>
        <RadioGroup v-model="interventionType" class="flex flex-col gap-2">
          <div class="flex items-center space-x-2">
            <RadioGroupItem value="Réparation" id="type-reparation" />
            <label for="type-reparation" class="cursor-pointer ml-2">Réparation</label>
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
        <RadioGroup v-model="documentType" class="flex flex-col gap-2">
          <div class="flex items-center space-x-2">
            <RadioGroupItem value="Devis" id="doc-devis" />
            <label for="doc-devis" class="cursor-pointer ml-2">Devis</label>
          </div>
          <div class="flex items-center space-x-2">
            <RadioGroupItem value="Facture" id="doc-facture" />
            <label for="doc-facture" class="cursor-pointer ml-2">Facture</label>
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
