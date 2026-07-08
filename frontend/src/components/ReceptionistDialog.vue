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
import { areStringsEqual } from '../utils/validation'
import type { Receptionist } from '../types/employee'

interface Props {
  open: boolean
  mode: 'create' | 'edit'
  receptionist?: Receptionist | null
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
      email: string
      password?: string
    },
  ]
  delete: []
}>()

const step = ref(1)
const hasAttemptedSubmit = ref(false)

// Watch dialog open/close
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      step.value = 1
      hasAttemptedSubmit.value = false
      if (props.mode === 'edit' && props.receptionist) {
        setStep1Values({
          lastName: props.receptionist.lastName,
          firstName: props.receptionist.firstName,
          birthDate: props.receptionist.birthDate,
          hireDate: props.receptionist.hireDate || '',
        })
        setStep2Values({
          email: props.receptionist.email,
          password: '',
          passwordConfirm: '',
        })
      } else {
        resetStep1Form()
        resetStep2Form()
      }
    }
  },
)

// Step 1 validation schema
const step1Schema = toTypedSchema(
  z.object({
    lastName: z.string().min(1, 'Nom requis').max(50, 'Le nom ne peut pas dépasser 50 caractères'),
    firstName: z
      .string()
      .min(1, 'Prénom requis')
      .max(50, 'Le prénom ne peut pas dépasser 50 caractères'),
    birthDate: z.string().min(1, 'Date de naissance requise'),
    hireDate: z.string().optional(),
  }),
)

// Step 2 validation schema
const step2Schema = toTypedSchema(
  z
    .object({
      email: z.string().email('Adresse email invalide').min(1, 'Email requis'),
      password: z.string().optional(),
      passwordConfirm: z.string().optional(),
    })
    .refine(
      (data) => {
        // Create mode: password required
        if (props.mode === 'create') {
          return data.password && data.password.length > 0 && data.password.length <= 50
        }
        // Edit mode: password optional, but if provided must be valid
        if (props.mode === 'edit') {
          if (!data.password || data.password.length === 0) {
            return true
          }
          return data.password.length <= 50
        }
        return true
      },
      {
        message: 'Mot de passe requis (max 50 caractères)',
        path: ['password'],
      },
    )
    .refine(
      (data) => {
        // Create mode: passwordConfirm required
        if (props.mode === 'create') {
          return (
            data.passwordConfirm &&
            data.passwordConfirm.length > 0 &&
            data.passwordConfirm.length <= 50
          )
        }
        // Edit mode: passwordConfirm optional, but if password is provided, confirm is required
        if (props.mode === 'edit') {
          if (!data.password || data.password.length === 0) {
            return true
          }
          return (
            data.passwordConfirm &&
            data.passwordConfirm.length > 0 &&
            data.passwordConfirm.length <= 50
          )
        }
        return true
      },
      {
        message: 'Confirmation requise (max 50 caractères)',
        path: ['passwordConfirm'],
      },
    )
    .refine(
      (data) => {
        // Only check match if password is provided
        if (!data.password || data.password.length === 0) {
          return true
        }
        return areStringsEqual(data.password, data.passwordConfirm || '')
      },
      {
        message: 'Les mots de passe ne correspondent pas',
        path: ['passwordConfirm'],
      },
    ),
)

const getStep1InitialValues = () => {
  if (props.mode === 'edit' && props.receptionist) {
    return {
      lastName: props.receptionist.lastName,
      firstName: props.receptionist.firstName,
      birthDate: props.receptionist.birthDate,
      hireDate: props.receptionist.hireDate || '',
    }
  }
  return {
    lastName: '',
    firstName: '',
    birthDate: '',
    hireDate: '',
  }
}

const getStep2InitialValues = () => {
  if (props.mode === 'edit' && props.receptionist) {
    return {
      email: props.receptionist.email,
      password: '',
      passwordConfirm: '',
    }
  }
  return {
    email: '',
    password: '',
    passwordConfirm: '',
  }
}

const {
  handleSubmit: handleStep1Submit,
  values: step1Values,
  errors: step1Errors,
  defineField: defineStep1Field,
  resetForm: resetStep1Form,
  setValues: setStep1Values,
} = useForm({
  validationSchema: step1Schema,
  initialValues: getStep1InitialValues(),
  validateOnMount: false,
})

const {
  handleSubmit: handleStep2Submit,
  values: step2Values,
  errors: step2Errors,
  defineField: defineStep2Field,
  resetForm: resetStep2Form,
  setValues: setStep2Values,
} = useForm({
  validationSchema: step2Schema,
  initialValues: getStep2InitialValues(),
  validateOnMount: false,
})

// Step 1 fields
const [lastName, lastNameAttrs] = defineStep1Field('lastName')
const [firstName, firstNameAttrs] = defineStep1Field('firstName')
const [birthDate, birthDateAttrs] = defineStep1Field('birthDate')
const [hireDate, hireDateAttrs] = defineStep1Field('hireDate')

// Step 2 fields
const [email, emailAttrs] = defineStep2Field('email')
const [password, passwordAttrs] = defineStep2Field('password')
const [passwordConfirm, passwordConfirmAttrs] = defineStep2Field('passwordConfirm')

const dialogTitle = computed(() => {
  const action = props.mode === 'edit' ? 'Modifier' : 'Ajouter'
  if (props.mode === 'edit') {
    return `${action} un réceptionniste`
  }
  return step.value === 1 ? 'Ajouter un réceptionniste (1/2)' : 'Ajouter un réceptionniste (2/2)'
})

const dialogDescription = computed(() => {
  if (props.mode === 'edit') {
    return 'Modifiez les informations du réceptionniste.'
  }
  return step.value === 1
    ? 'Remplissez les informations personnelles.'
    : 'Remplissez les identifiants de connexion.'
})

function handleClose() {
  step.value = 1
  emit('close')
}

function handlePrevious() {
  step.value = 1
}

function handleDelete() {
  emit('delete')
}

const onStep1Submit = handleStep1Submit(
  () => {
    step.value = 2
  },
  () => {
    hasAttemptedSubmit.value = true
  },
)

const onStep2Submit = handleStep2Submit(
  (vals) => {
    emit('submit', {
      lastName: step1Values.lastName!,
      firstName: step1Values.firstName!,
      birthDate: step1Values.birthDate!,
      hireDate: step1Values.hireDate,
      email: vals.email,
      password: vals.password,
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

      <!-- Step 1 Form -->
      <form v-if="step === 1" @submit="onStep1Submit" class="grid gap-3 py-0">
        <!-- Nom -->
        <FieldGroup class="gap-2">
          <FieldLabel for="lastName">Nom</FieldLabel>
          <Field>
            <Input id="lastName" v-model="lastName" v-bind="lastNameAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.lastName">{{
              step1Errors.lastName
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Prénom -->
        <FieldGroup class="gap-2">
          <FieldLabel for="firstName">Prénom</FieldLabel>
          <Field>
            <Input id="firstName" v-model="firstName" v-bind="firstNameAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.firstName">{{
              step1Errors.firstName
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Date de naissance -->
        <FieldGroup class="gap-2">
          <FieldLabel for="birthDate">Date de naissance</FieldLabel>
          <Field>
            <Input id="birthDate" v-model="birthDate" v-bind="birthDateAttrs" type="date" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.birthDate">{{
              step1Errors.birthDate
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Date de démarrage -->
        <FieldGroup class="gap-2">
          <FieldLabel for="hireDate">Date de démarrage (optionnel)</FieldLabel>
          <Field>
            <Input id="hireDate" v-model="hireDate" v-bind="hireDateAttrs" type="date" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.hireDate">{{
              step1Errors.hireDate
            }}</FieldError>
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
            <Button type="submit">Suivant</Button>
          </div>
        </DialogFooter>
      </form>

      <!-- Step 2 Form -->
      <form v-if="step === 2" @submit="onStep2Submit" class="grid gap-4 py-4">
        <!-- Email -->
        <FieldGroup class="gap-2">
          <FieldLabel for="email">Email</FieldLabel>
          <Field>
            <Input id="email" v-model="email" v-bind="emailAttrs" type="email" />
            <FieldError v-if="hasAttemptedSubmit && step2Errors.email">{{
              step2Errors.email
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Password -->
        <FieldGroup class="gap-2">
          <FieldLabel for="password">Mot de passe</FieldLabel>
          <Field>
            <Input id="password" v-model="password" v-bind="passwordAttrs" type="password" />
            <FieldError v-if="hasAttemptedSubmit && step2Errors.password">{{
              step2Errors.password
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Password Confirmation -->
        <FieldGroup class="gap-2">
          <FieldLabel for="passwordConfirm">Confirmer le mot de passe</FieldLabel>
          <Field>
            <Input
              id="passwordConfirm"
              v-model="passwordConfirm"
              v-bind="passwordConfirmAttrs"
              type="password"
            />
            <FieldError v-if="hasAttemptedSubmit && step2Errors.passwordConfirm">{{
              step2Errors.passwordConfirm
            }}</FieldError>
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
            <Button type="button" variant="outline" @click="handlePrevious">Précédent</Button>
            <Button type="submit">{{ mode === 'edit' ? 'Modifier' : 'Ajouter' }}</Button>
          </div>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
