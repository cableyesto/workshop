<script setup lang="ts">
import { ref, computed } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { useMechanicsQuery } from '../../api/employees'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
import MechanicCombobox from './MechanicCombobox.vue'

interface Props {
  mechanicId: number | undefined
  licensePlate: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:mechanicId': [value: number | undefined]
  'update:licensePlate': [value: string]
  search: []
}>()

const hasAttemptedSubmit = ref(false)

// Fetch mechanics
const { data: mechanics, isLoading: mechanicsLoading } = useMechanicsQuery()

// Computed mechanic options for combobox
const mechanicOptions = computed(() => {
  if (!mechanics.value) return []
  return mechanics.value.map((m) => ({
    value: m.id,
    label: `${m.firstName} ${m.lastName}`,
  }))
})

// Validation schema
const schema = toTypedSchema(
  z.object({
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
  }),
)

const { handleSubmit, errors, defineField } = useForm({
  validationSchema: schema,
  initialValues: {
    mechanicId: props.mechanicId,
    licensePlate: props.licensePlate,
  },
  validateOnMount: false,
})

const [mechanicId] = defineField('mechanicId')
const [licensePlate] = defineField('licensePlate', {
  validateOnModelUpdate: false,
})

const onSubmit = handleSubmit(
  () => {
    emit('search')
  },
  () => {
    hasAttemptedSubmit.value = true
  },
)

function formatLicensePlate(event: Event) {
  // Clear validation errors when user types
  hasAttemptedSubmit.value = false

  const input = event.target as HTMLInputElement
  let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '')

  if (value.length > 2 && value.length <= 5) {
    value = value.slice(0, 2) + '-' + value.slice(2)
  } else if (value.length > 5) {
    value = value.slice(0, 2) + '-' + value.slice(2, 5) + '-' + value.slice(5, 7)
  }

  // Update both vee-validate field and emit to parent
  licensePlate.value = value
  emit('update:licensePlate', value)
}

function updateMechanicId(id: number | undefined) {
  mechanicId.value = id
  emit('update:mechanicId', id)
}
</script>

<template>
  <form @submit="onSubmit" class="grid gap-6">
    <!-- Mechanic ComboBox -->
    <FieldGroup class="gap-2">
      <FieldLabel for="mechanic">Mécanicien</FieldLabel>
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
          class="w-96"
          @input="formatLicensePlate"
        />
        <FieldError v-if="hasAttemptedSubmit && errors.licensePlate">{{
          errors.licensePlate
        }}</FieldError>
      </Field>
    </FieldGroup>

    <Button type="submit">Rechercher</Button>
  </form>
</template>
