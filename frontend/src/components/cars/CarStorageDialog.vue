<script setup lang="ts">
import { watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, Field as VeeField } from 'vee-validate'
import { z } from 'zod'
import { useUpdateCarStorageByLicensePlateMutation } from '../../api/cars'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Field, FieldLabel, FieldError } from '@/components/ui/field'

interface Props {
  open: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  success: []
}>()

const schema = toTypedSchema(
  z.object({
    licensePlate: z
      .string()
      .length(9, 'La plaque doit contenir exactement 9 caractères')
      .regex(/^[A-Z]{2}-\d{3}-[A-Z]{2}$/, 'Format invalide. Attendu: AB-123-CD'),
  }),
)

const { handleSubmit, resetForm, setFieldValue, setErrors } = useForm({
  validationSchema: schema,
  validateOnMount: false,
  initialValues: {
    licensePlate: '',
  },
})

const { mutate: updateCarStorage } = useUpdateCarStorageByLicensePlateMutation(
  () => {
    resetForm()
    emit('success')
    emit('close')
  },
  (error) => {
    setErrors({
      licensePlate: error.message || 'Aucune fiche trouvée pour cette plaque',
    })
  },
)

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      resetForm()
    }
  },
)

const onSubmit = handleSubmit((formValues) => {
  updateCarStorage({
    licensePlate: formValues.licensePlate,
    isStored: true,
  })
})

function handleCancel() {
  emit('close')
  resetForm()
}

function formatLicensePlate(event: Event) {
  const input = event.target as HTMLInputElement
  let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '')

  if (value.length > 2 && value.length <= 5) {
    value = value.slice(0, 2) + '-' + value.slice(2)
  } else if (value.length > 5) {
    value = value.slice(0, 2) + '-' + value.slice(2, 5) + '-' + value.slice(5, 7)
  }

  setFieldValue('licensePlate', value)
}
</script>

<template>
  <Dialog :open="open" @update:open="(val) => !val && handleCancel()">
    <DialogContent class="sm:max-w-[500px]">
      <form @submit="onSubmit">
        <DialogHeader>
          <DialogTitle>Ajouter une voiture au dépôt</DialogTitle>
          <DialogDescription>
            Saisissez la plaque d'immatriculation et indiquez si la voiture est stockée.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-6 py-4">
          <VeeField v-slot="{ field, errors }" name="licensePlate">
            <Field :data-invalid="!!errors.length">
              <FieldLabel for="licensePlate"> Plaque d'immatriculation </FieldLabel>
              <Input
                id="licensePlate"
                v-bind="field"
                placeholder="AB-123-CD"
                :aria-invalid="!!errors.length"
                @input="formatLicensePlate"
                maxlength="9"
              />
              <FieldError v-if="errors.length" :errors="errors" />
            </Field>
          </VeeField>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>
          <Button type="submit">Ajouter</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
