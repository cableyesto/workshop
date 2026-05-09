<script setup lang="ts">
import { ref, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
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
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
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

const hasAttemptedSubmit = ref(false)

// Watch dialog open/close
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      hasAttemptedSubmit.value = false
      if (props.client) {
        setValues({
          lastName: props.client.lastName,
          firstName: props.client.firstName,
          email: props.client.email || '',
          phone: props.client.phone,
        })
      } else {
        resetForm()
      }
    }
  },
)

// Validation schema
const schema = toTypedSchema(
  z.object({
    lastName: z.string().min(1, 'Le nom est requis'),
    firstName: z.string().min(1, 'Le prénom est requis'),
    email: z.string().email('Email invalide').or(z.literal('')),
    phone: z.string().min(1, 'Le téléphone est requis'),
  }),
)

const getInitialValues = () => {
  if (props.client) {
    return {
      lastName: props.client.lastName,
      firstName: props.client.firstName,
      email: props.client.email || '',
      phone: props.client.phone,
    }
  }
  return {
    lastName: '',
    firstName: '',
    email: '',
    phone: '',
  }
}

const { handleSubmit, errors, defineField, resetForm, setValues } = useForm({
  validationSchema: schema,
  initialValues: getInitialValues(),
  validateOnMount: false,
})

// Fields
const [lastName, lastNameAttrs] = defineField('lastName')
const [firstName, firstNameAttrs] = defineField('firstName')
const [email, emailAttrs] = defineField('email')
const [phone, phoneAttrs] = defineField('phone')

const { mutate: updateClient, isLoading } = useUpdateClientMutation(
  () => {
    emit('success')
    emit('close')
  },
  (error) => {
    hasAttemptedSubmit.value = true
  },
)

function handleClose() {
  emit('close')
}

const onSubmit = handleSubmit(
  (vals) => {
    if (!props.client) return

    updateClient({
      clientId: props.client.id,
      data: {
        firstName: vals.firstName,
        lastName: vals.lastName,
        email: vals.email || null,
        phone: vals.phone,
      },
    })
  },
  () => {
    hasAttemptedSubmit.value = true
  },
)
</script>

<template>
  <Dialog :open="open" @update:open="(isOpen) => !isOpen && handleClose()">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>Modifier le client</DialogTitle>
        <DialogDescription>Modifiez les informations du client</DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit" class="grid gap-3 py-0">
        <!-- Nom -->
        <FieldGroup class="gap-2">
          <FieldLabel for="lastName">Nom</FieldLabel>
          <Field>
            <Input id="lastName" v-model="lastName" v-bind="lastNameAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && errors.lastName">{{
              errors.lastName
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Prénom -->
        <FieldGroup class="gap-2">
          <FieldLabel for="firstName">Prénom</FieldLabel>
          <Field>
            <Input id="firstName" v-model="firstName" v-bind="firstNameAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && errors.firstName">{{
              errors.firstName
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Email -->
        <FieldGroup class="gap-2">
          <FieldLabel for="email">Email</FieldLabel>
          <Field>
            <Input id="email" v-model="email" v-bind="emailAttrs" type="email" />
            <FieldError v-if="hasAttemptedSubmit && errors.email">{{ errors.email }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Téléphone -->
        <FieldGroup class="gap-2">
          <FieldLabel for="phone">Téléphone</FieldLabel>
          <Field>
            <Input id="phone" v-model="phone" v-bind="phoneAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && errors.phone">{{ errors.phone }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Annuler</Button>
          <Button type="submit" :disabled="isLoading">
            {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
