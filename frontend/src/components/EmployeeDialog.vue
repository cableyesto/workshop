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
import type { EmployeeFormData, Mechanic, Receptionist } from '../types/employee'

interface Props {
  open: boolean
  type: 'mechanic' | 'receptionist'
  mode: 'create' | 'edit'
  employee?: Mechanic | Receptionist | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  submit: [data: EmployeeFormData]
}>()

const step = ref(1)

// Step 1 validation schema
const step1Schema = toTypedSchema(
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
        // If mechanic, PIN must be exactly 4 digits
        if (props.type === 'mechanic') {
          return /^\d{4}$/.test(data.pin || '')
        }
        return true
      },
      {
        message: 'Le code PIN doit contenir exactement 4 chiffres',
        path: ['pin'],
      },
    ),
)

// Step 2 validation schema
const step2Schema = toTypedSchema(
  z
    .object({
      email: z.string().email('Adresse email invalide').min(1, 'Email requis'),
      password: z
        .string()
        .min(1, 'Mot de passe requis')
        .max(50, 'Le mot de passe ne peut pas dépasser 50 caractères'),
      passwordConfirm: z
        .string()
        .min(1, 'Confirmation requise')
        .max(50, 'Le mot de passe ne peut pas dépasser 50 caractères'),
    })
    .refine((data) => areStringsEqual(data.password, data.passwordConfirm), {
      message: 'Les mots de passe ne correspondent pas',
      path: ['passwordConfirm'],
    }),
)

const getStep1InitialValues = () => {
  if (props.mode === 'edit' && props.employee) {
    return {
      lastName: props.employee.lastName,
      firstName: props.employee.firstName,
      birthDate: props.employee.birthDate,
      hireDate: props.employee.hireDate || '',
      pin: '', // PIN not returned from API, leave empty for now
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
})

// Watch for employee changes to reset form
watch(
  () => props.employee,
  (newEmployee) => {
    if (newEmployee && props.mode === 'edit') {
      setStep1Values({
        lastName: newEmployee.lastName,
        firstName: newEmployee.firstName,
        birthDate: newEmployee.birthDate,
        hireDate: newEmployee.hireDate || '',
        pin: '',
      })
    }
  },
  { immediate: true },
)

const getStep2InitialValues = () => {
  if (props.mode === 'edit' && props.employee && 'email' in props.employee) {
    return {
      email: props.employee.email,
      password: '', // Don't pre-fill password
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
  handleSubmit: handleStep2Submit,
  values: step2Values,
  errors: step2Errors,
  defineField: defineStep2Field,
  resetForm: resetStep2Form,
  setValues: setStep2Values,
} = useForm({
  validationSchema: step2Schema,
  initialValues: getStep2InitialValues(),
})

// Watch for employee changes to reset step 2 form (receptionist email)
watch(
  () => props.employee,
  (newEmployee) => {
    if (newEmployee && props.mode === 'edit' && 'email' in newEmployee) {
      setStep2Values({
        email: newEmployee.email,
        password: '',
        passwordConfirm: '',
      })
    }
  },
  { immediate: true },
)

// Step 1 fields
const [lastName, lastNameAttrs] = defineStep1Field('lastName')
const [firstName, firstNameAttrs] = defineStep1Field('firstName')
const [birthDate, birthDateAttrs] = defineStep1Field('birthDate')
const [hireDate, hireDateAttrs] = defineStep1Field('hireDate')
const [pin, pinAttrs] = defineStep1Field('pin')

// Step 2 fields
const [email, emailAttrs] = defineStep2Field('email')
const [password, passwordAttrs] = defineStep2Field('password')
const [passwordConfirm, passwordConfirmAttrs] = defineStep2Field('passwordConfirm')

const dialogTitle = computed(() => {
  const action = props.mode === 'edit' ? 'Modifier' : 'Ajouter'

  if (props.type === 'mechanic') {
    return `${action} un mécanicien`
  }
  if (props.mode === 'edit') {
    return `${action} un réceptionniste`
  }
  return step.value === 1 ? 'Ajouter un réceptionniste (1/2)' : 'Ajouter un réceptionniste (2/2)'
})

const dialogDescription = computed(() => {
  if (props.mode === 'edit') {
    return 'Modifiez les informations de l\'employé.'
  }
  if (props.type === 'mechanic') {
    return 'Remplissez les informations du nouveau mécanicien.'
  }
  return step.value === 1
    ? 'Remplissez les informations personnelles.'
    : 'Remplissez les identifiants de connexion.'
})

function handleClose() {
  step.value = 1
  resetStep1Form()
  resetStep2Form()
  emit('close')
}

function handlePrevious() {
  step.value = 1
}

function handleDelete() {
  // TODO: Implement delete functionality
  console.log('Delete employee - to be implemented')
  alert('Fonction de suppression à implémenter')
}

const onStep1Submit = handleStep1Submit((values) => {
  if (props.type === 'mechanic') {
    // Submit mechanic form
    const data: EmployeeFormData = {
      type: 'mechanic',
      lastName: values.lastName,
      firstName: values.firstName,
      birthDate: values.birthDate,
      hireDate: values.hireDate,
      pin: values.pin,
    }
    emit('submit', data)
    // Don't close here - let parent handle it based on mutation result
  } else {
    // Receptionist: go to step 2
    step.value = 2
  }
})

const onStep2Submit = handleStep2Submit((values) => {
  // Submit receptionist form with both steps data
  const data: EmployeeFormData = {
    type: 'receptionist',
    lastName: step1Values.lastName!,
    firstName: step1Values.firstName!,
    birthDate: step1Values.birthDate!,
    hireDate: step1Values.hireDate,
    email: values.email,
    password: values.password,
  }
  emit('submit', data)
  // Don't close here - let parent handle it based on mutation result
})
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
            <FieldError v-if="step1Errors.lastName">{{ step1Errors.lastName }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Prénom -->
        <FieldGroup class="gap-2">
          <FieldLabel for="firstName">Prénom</FieldLabel>
          <Field>
            <Input id="firstName" v-model="firstName" v-bind="firstNameAttrs" type="text" />
            <FieldError v-if="step1Errors.firstName">{{ step1Errors.firstName }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Date de naissance -->
        <FieldGroup class="gap-2">
          <FieldLabel for="birthDate">Date de naissance</FieldLabel>
          <Field>
            <Input id="birthDate" v-model="birthDate" v-bind="birthDateAttrs" type="date" />
            <FieldError v-if="step1Errors.birthDate">{{ step1Errors.birthDate }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Date de démarrage -->
        <FieldGroup class="gap-2">
          <FieldLabel for="hireDate">Date de démarrage (optionnel)</FieldLabel>
          <Field>
            <Input id="hireDate" v-model="hireDate" v-bind="hireDateAttrs" type="date" />
            <FieldError v-if="step1Errors.hireDate">{{ step1Errors.hireDate }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Code PIN (mechanic only) -->
        <FieldGroup v-if="type === 'mechanic'" class="gap-2">
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
            <FieldError v-if="step1Errors.pin">{{ step1Errors.pin }}</FieldError>
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
            <Button type="submit">
              {{
                type === 'mechanic'
                  ? mode === 'edit'
                    ? 'Modifier'
                    : 'Ajouter'
                  : 'Suivant'
              }}
            </Button>
          </div>
        </DialogFooter>
      </form>

      <!-- Step 2 Form (Receptionist only) -->
      <form
        v-if="step === 2 && type === 'receptionist'"
        @submit="onStep2Submit"
        class="grid gap-4 py-4"
      >
        <!-- Email -->
        <FieldGroup class="gap-2">
          <FieldLabel for="email">Email</FieldLabel>
          <Field>
            <Input id="email" v-model="email" v-bind="emailAttrs" type="email" />
            <FieldError v-if="step2Errors.email">{{ step2Errors.email }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Password -->
        <FieldGroup class="gap-2">
          <FieldLabel for="password">Mot de passe</FieldLabel>
          <Field>
            <Input id="password" v-model="password" v-bind="passwordAttrs" type="password" />
            <FieldError v-if="step2Errors.password">{{ step2Errors.password }}</FieldError>
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
            <FieldError v-if="step2Errors.passwordConfirm">{{
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
