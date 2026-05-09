<script setup lang="ts">
import { ref, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, Field as VeeField } from 'vee-validate'
import { z } from 'zod'
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
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Label } from '@/components/ui/label'

interface Props {
  open: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  submit: [data: { licensePlate: string; isStored: boolean }]
}>()

const schema = toTypedSchema(
  z.object({
    licensePlate: z
      .string()
      .length(9, 'La plaque doit contenir exactement 9 caractères')
      .regex(/^[A-Z]{2}-\d{3}-[A-Z]{2}$/, 'Format invalide. Attendu: AB-123-CD'),
    isStored: z.enum(['oui', 'non']),
  }),
)

const { handleSubmit, resetForm, setFieldValue } = useForm({
  validationSchema: schema,
  validateOnMount: false,
  initialValues: {
    licensePlate: '',
    isStored: 'oui',
  },
})

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      resetForm()
    }
  },
)

const onSubmit = handleSubmit((formValues) => {
  emit('submit', {
    licensePlate: formValues.licensePlate,
    isStored: formValues.isStored === 'oui',
  })
  resetForm()
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

          <VeeField v-slot="{ field, errors }" name="isStored">
            <Field :data-invalid="!!errors.length">
              <FieldLabel> Voiture stockée au dépôt ? </FieldLabel>
              <RadioGroup default-value="non" v-bind="field">
                <div class="flex items-center space-x-2">
                  <RadioGroupItem id="stored-yes" value="oui" />
                  <Label for="stored-yes" class="cursor-pointer">Oui</Label>
                </div>
                <div class="flex items-center space-x-2">
                  <RadioGroupItem id="stored-no" value="non" />
                  <Label for="stored-no" class="cursor-pointer">Non</Label>
                </div>
              </RadioGroup>
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
