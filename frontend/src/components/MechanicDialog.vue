<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
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
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
import type { Mechanic } from '../types/employee'

interface Props {
  open: boolean
  mode: 'create' | 'edit'
  mechanic?: Mechanic | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  submit: [
    data: {
      lastName: string
      firstName: string
      birthDate: string
      hireDate?: string
      pin?: string
    },
  ]
  delete: []
}>()

const hasAttemptedSubmit = ref(false)

// Watch dialog open/close
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      hasAttemptedSubmit.value = false
      if (props.mode === 'edit' && props.mechanic) {
        setValues({
          lastName: props.mechanic.lastName,
          firstName: props.mechanic.firstName,
          birthDate: props.mechanic.birthDate,
          hireDate: props.mechanic.hireDate || '',
          pin: '',
        })
      } else {
        resetForm()
      }
    }
  },
)

// Validation schema
const schema = toTypedSchema(
  z
    .object({
      lastName: z
        .string()
        .min(1, 'Nom requis')
        .max(50, 'Le nom ne peut pas dépasser 50 caractères'),
      firstName: z
        .string()
        .min(1, 'Prénom requis')
        .max(50, 'Le prénom ne peut pas dépasser 50 caractères'),
      birthDate: z.string().min(1, 'Date de naissance requise'),
      hireDate: z.string().optional(),
      pin: z.string().optional(),
    })
    .refine(
      (data) => {
        // Create mode: PIN required
        if (props.mode === 'create') {
          return /^\d{4}$/.test(data.pin || '')
        }
        // Edit mode: PIN optional, but if provided must be valid
        if (props.mode === 'edit') {
          if (!data.pin || data.pin.length === 0) {
            return true
          }
          return /^\d{4}$/.test(data.pin)
        }
        return true
      },
      {
        message: 'Le code PIN doit contenir exactement 4 chiffres',
        path: ['pin'],
      },
    ),
)

const getInitialValues = () => {
  if (props.mode === 'edit' && props.mechanic) {
    return {
      lastName: props.mechanic.lastName,
      firstName: props.mechanic.firstName,
      birthDate: props.mechanic.birthDate,
      hireDate: props.mechanic.hireDate || '',
      pin: '',
    }
  }
  return {
    lastName: '',
    firstName: '',
    birthDate: '',
    hireDate: '',
    pin: '',
  }
}

const { handleSubmit, values, errors, defineField, resetForm, setValues } = useForm({
  validationSchema: schema,
  initialValues: getInitialValues(),
  validateOnMount: false,
})

// Fields
const [lastName, lastNameAttrs] = defineField('lastName')
const [firstName, firstNameAttrs] = defineField('firstName')
const [birthDate, birthDateAttrs] = defineField('birthDate')
const [hireDate, hireDateAttrs] = defineField('hireDate')
const [pin, pinAttrs] = defineField('pin')

const dialogTitle = computed(() => {
  const action = props.mode === 'edit' ? 'Modifier' : 'Ajouter'
  return `${action} un mécanicien`
})

const dialogDescription = computed(() => {
  if (props.mode === 'edit') {
    return 'Modifiez les informations du mécanicien.'
  }
  return 'Remplissez les informations du nouveau mécanicien.'
})

function handleClose() {
  emit('close')
}

function handleDelete() {
  emit('delete')
}

const onSubmit = handleSubmit(
  (vals) => {
    emit('submit', {
      lastName: vals.lastName,
      firstName: vals.firstName,
      birthDate: vals.birthDate,
      hireDate: vals.hireDate,
      pin: vals.pin,
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
        <DialogTitle>{{ dialogTitle }}</DialogTitle>
        <DialogDescription>{{ dialogDescription }}</DialogDescription>
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

        <!-- Date de naissance -->
        <FieldGroup class="gap-2">
          <FieldLabel for="birthDate">Date de naissance</FieldLabel>
          <Field>
            <Input id="birthDate" v-model="birthDate" v-bind="birthDateAttrs" type="date" />
            <FieldError v-if="hasAttemptedSubmit && errors.birthDate">{{
              errors.birthDate
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Date de démarrage -->
        <FieldGroup class="gap-2">
          <FieldLabel for="hireDate">Date de démarrage (optionnel)</FieldLabel>
          <Field>
            <Input id="hireDate" v-model="hireDate" v-bind="hireDateAttrs" type="date" />
            <FieldError v-if="hasAttemptedSubmit && errors.hireDate">{{
              errors.hireDate
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Code PIN -->
        <FieldGroup class="gap-2">
          <FieldLabel for="pin">Code PIN (4 chiffres)</FieldLabel>
          <Field>
            <Input
              id="pin"
              v-model="pin"
              v-bind="pinAttrs"
              type="text"
              maxlength="4"
              inputmode="numeric"
              placeholder="0000"
            />
            <FieldError v-if="hasAttemptedSubmit && errors.pin">{{ errors.pin }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter class="flex justify-between">
          <div>
            <Button
              v-if="mode === 'edit'"
              type="button"
              variant="destructive"
              @click="handleDelete"
            >
              Supprimer
            </Button>
          </div>
          <div class="flex gap-2">
            <Button type="button" variant="outline" @click="handleClose">Annuler</Button>
            <Button type="submit">{{ mode === 'edit' ? 'Modifier' : 'Ajouter' }}</Button>
          </div>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
