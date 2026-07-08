<script setup lang="ts">
import { ref, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Textarea } from '@/components/ui/textarea'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { useInterventionQuery, useUpdateInterventionMutation } from '@/api/interventions'

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

// Validation schema - all fields optional
const schema = toTypedSchema(
  z.object({
    hasClientRemark: z.enum(['false', 'true']),
    clientRequest: z.string().optional(),
    hasInterventionEndRemark: z.enum(['false', 'true']),
    finalNote: z.string().optional(),
  }),
)

const { handleSubmit, defineField, setValues } = useForm({
  validationSchema: schema,
  initialValues: {
    hasClientRemark: 'false',
    clientRequest: '',
    hasInterventionEndRemark: 'false',
    finalNote: '',
  },
  validateOnMount: false,
})

const [hasClientRemark] = defineField('hasClientRemark')
const [clientRequest] = defineField('clientRequest')
const [hasInterventionEndRemark] = defineField('hasInterventionEndRemark')
const [finalNote] = defineField('finalNote')

// Fetch intervention data (cached from Step 2)
const { data: interventionData } = useInterventionQuery(props.interventionId, props.isEditMode)

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
watch(
  interventionData,
  (data) => {
    if (data && props.isEditMode) {
      setValues({
        hasClientRemark: data.clientRemark ? 'true' : 'false',
        clientRequest: data.clientRequest || '',
        hasInterventionEndRemark: data.interventionEndRemark ? 'true' : 'false',
        finalNote: data.finalNote || '',
      })
    }
  },
  { immediate: true },
)

const onSubmit = handleSubmit(async (values) => {
  if (!props.interventionId) {
    alert('Erreur: ID intervention manquant')
    return
  }

  updateIntervention({
    interventionId: props.interventionId,
    data: {
      clientRequest:
        values.hasClientRemark === 'true' && values.clientRequest
          ? values.clientRequest
          : undefined,
      finalNote:
        values.hasInterventionEndRemark === 'true' && values.finalNote
          ? values.finalNote
          : undefined,
    },
  })
})
</script>

<template>
  <form @submit="onSubmit" class="grid gap-6">
    <h2 class="text-2xl font-semibold">Étape 3: Remarques</h2>

    <!-- Breadcrumb -->
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem>
          <span class="text-muted-foreground">Type</span>
        </BreadcrumbItem>
        <BreadcrumbSeparator />
        <BreadcrumbItem>
          <BreadcrumbPage>Remarque</BreadcrumbPage>
        </BreadcrumbItem>
        <BreadcrumbSeparator />
        <BreadcrumbItem>
          <span class="text-muted-foreground">Acte</span>
        </BreadcrumbItem>
      </BreadcrumbList>
    </Breadcrumb>

    <div class="grid grid-cols-2 gap-6">
      <!-- Left Column: Client Remarks -->
      <div class="grid gap-6">
        <!-- Client Remark Toggle -->
        <FieldGroup class="gap-2">
          <FieldLabel>Remarque client</FieldLabel>
          <Field>
            <RadioGroup v-model="hasClientRemark" class="flex gap-4">
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="false" id="client-remark-non" />
                <label for="client-remark-non" class="cursor-pointer">Non</label>
              </div>
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="true" id="client-remark-oui" />
                <label for="client-remark-oui" class="cursor-pointer">Oui</label>
              </div>
            </RadioGroup>
          </Field>
        </FieldGroup>

        <!-- Client Request Textarea -->
        <FieldGroup class="gap-2">
          <FieldLabel for="clientRequest">Besoin client</FieldLabel>
          <Field>
            <Textarea
              id="clientRequest"
              v-model="clientRequest"
              :disabled="hasClientRemark === 'false'"
              placeholder="Text area"
              rows="6"
              class="resize-none"
            />
          </Field>
        </FieldGroup>
      </div>

      <!-- Right Column: Intervention End Remarks -->
      <div class="grid gap-6">
        <!-- Intervention End Remark Toggle -->
        <FieldGroup class="gap-2">
          <FieldLabel>Remarque fin d'intervention</FieldLabel>
          <Field>
            <RadioGroup v-model="hasInterventionEndRemark" class="flex gap-4">
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="false" id="end-remark-non" />
                <label for="end-remark-non" class="cursor-pointer">Non</label>
              </div>
              <div class="flex items-center space-x-2">
                <RadioGroupItem value="true" id="end-remark-oui" />
                <label for="end-remark-oui" class="cursor-pointer">Oui</label>
              </div>
            </RadioGroup>
          </Field>
        </FieldGroup>

        <!-- Final Note Textarea -->
        <FieldGroup class="gap-2">
          <FieldLabel for="finalNote">Fin intervention</FieldLabel>
          <Field>
            <Textarea
              id="finalNote"
              v-model="finalNote"
              :disabled="hasInterventionEndRemark === 'false'"
              placeholder="Text area"
              rows="6"
              class="resize-none"
            />
          </Field>
        </FieldGroup>
      </div>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-2 gap-6">
      <Button type="button" variant="outline" @click="$emit('back')">Précédent</Button>
      <Button type="submit">Suivant</Button>
    </div>
  </form>
</template>
