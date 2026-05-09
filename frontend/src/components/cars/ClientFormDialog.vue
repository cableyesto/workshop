<script setup lang="ts">
import { watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, Field as VeeField } from 'vee-validate'
import { z } from 'zod'
import { useUpdateClientMutation } from '../../api/clients'
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
import type { Client } from '../../types/client'

interface Props {
  open: boolean
  client: Client | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  success: []
}>()

const schema = toTypedSchema(
  z.object({
    firstName: z.string().min(1, 'Le prénom est requis'),
    lastName: z.string().min(1, 'Le nom est requis'),
    email: z.string().email('Email invalide').nullable().or(z.literal('')),
    phone: z.string().min(1, 'Le téléphone est requis'),
  }),
)

const { handleSubmit, resetForm, setErrors, setValues } = useForm({
  validationSchema: schema,
  validateOnMount: false,
  initialValues: {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
  },
})

const { mutate: updateClient, isPending } = useUpdateClientMutation(
  () => {
    emit('success')
    emit('close')
  },
  (error) => {
    setErrors({
      firstName: error.message || 'Erreur lors de la mise à jour',
    })
  },
)

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen && props.client) {
      setValues({
        firstName: props.client.firstName,
        lastName: props.client.lastName,
        email: props.client.email || '',
        phone: props.client.phone,
      })
    } else {
      resetForm()
    }
  },
)

const onSubmit = handleSubmit((formValues) => {
  if (!props.client) return

  updateClient({
    clientId: props.client.id,
    data: {
      firstName: formValues.firstName,
      lastName: formValues.lastName,
      email: formValues.email || null,
      phone: formValues.phone,
    },
  })
})

function handleCancel() {
  emit('close')
  resetForm()
}
</script>

<template>
  <Dialog :open="open" @update:open="(val) => !val && handleCancel()">
    <DialogContent class="sm:max-w-[500px]">
      <form @submit="onSubmit">
        <DialogHeader>
          <DialogTitle>Modifier le client</DialogTitle>
          <DialogDescription>Modifiez les informations du client</DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <VeeField v-slot="{ field, errors }" name="lastName">
            <Field :data-invalid="!!errors.length">
              <FieldLabel for="lastName">Nom</FieldLabel>
              <Input
                id="lastName"
                v-bind="field"
                :aria-invalid="!!errors.length"
              />
              <FieldError v-if="errors.length" :errors="errors" />
            </Field>
          </VeeField>

          <VeeField v-slot="{ field, errors }" name="firstName">
            <Field :data-invalid="!!errors.length">
              <FieldLabel for="firstName">Prénom</FieldLabel>
              <Input
                id="firstName"
                v-bind="field"
                :aria-invalid="!!errors.length"
              />
              <FieldError v-if="errors.length" :errors="errors" />
            </Field>
          </VeeField>

          <VeeField v-slot="{ field, errors }" name="email">
            <Field :data-invalid="!!errors.length">
              <FieldLabel for="email">Email</FieldLabel>
              <Input
                id="email"
                type="email"
                v-bind="field"
                :aria-invalid="!!errors.length"
              />
              <FieldError v-if="errors.length" :errors="errors" />
            </Field>
          </VeeField>

          <VeeField v-slot="{ field, errors }" name="phone">
            <Field :data-invalid="!!errors.length">
              <FieldLabel for="phone">Téléphone</FieldLabel>
              <Input
                id="phone"
                v-bind="field"
                :aria-invalid="!!errors.length"
              />
              <FieldError v-if="errors.length" :errors="errors" />
            </Field>
          </VeeField>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleCancel">Annuler</Button>
          <Button type="submit" :disabled="isPending">
            {{ isPending ? 'Enregistrement...' : 'Enregistrer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
