<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { useCreateCarWithClientMutation } from '../../api/cars'
import { useManufacturersQuery } from '../../api/manufacturers'
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
import ManufacturerCombobox from './ManufacturerCombobox.vue'

interface Props {
  open: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  success: []
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
      resetStep1Form()
      resetStep2Form()
      resetStep3Form()
    }
  },
)

// Step 1 validation schema (Client)
const step1Schema = toTypedSchema(
  z.object({
    lastName: z.string().min(1, 'Le nom est requis'),
    firstName: z.string().min(1, 'Le prénom est requis'),
    email: z.string().email('Email invalide').or(z.literal('')),
    phone: z
      .string()
      .min(1, 'Le téléphone est requis')
      .regex(/^[0-9\s\-+().]+$/, 'Format de téléphone invalide'),
  }),
)

// Step 2 validation schema (Car basic info)
const step2Schema = toTypedSchema(
  z.object({
    manufacturer: z.string().min(1, 'La marque est requise'),
    model: z.string().min(1, 'Le modèle est requis'),
    licensePlate: z
      .string()
      .length(9, 'La plaque doit contenir exactement 9 caractères')
      .regex(/^[A-Z]{2}-\d{3}-[A-Z]{2}$/, 'Format invalide. Attendu: AB-123-CD'),
    color: z.string().min(1, 'La couleur est requise'),
  }),
)

// Step 3 validation schema (Car additional info)
const step3Schema = toTypedSchema(
  z.object({
    registrationYear: z
      .union([z.coerce.number().int().min(1900).max(2100), z.literal('')])
      .optional(),
    registrationMonth: z.union([z.coerce.number().int().min(1).max(12), z.literal('')]).optional(),
    mileage: z.union([z.coerce.number().int().min(0), z.literal('')]).optional(),
  }),
)

const getStep1InitialValues = () => ({
  lastName: '',
  firstName: '',
  email: '',
  phone: '',
})

const getStep2InitialValues = () => ({
  manufacturer: '',
  model: '',
  licensePlate: '',
  color: '',
})

const getStep3InitialValues = () => ({
  registrationYear: '' as const,
  registrationMonth: '' as const,
  mileage: '' as const,
})

const {
  handleSubmit: handleStep1Submit,
  values: step1Values,
  errors: step1Errors,
  defineField: defineStep1Field,
  resetForm: resetStep1Form,
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
  setErrors: setStep2Errors,
} = useForm({
  validationSchema: step2Schema,
  initialValues: getStep2InitialValues(),
  validateOnMount: false,
})

const {
  handleSubmit: handleStep3Submit,
  errors: step3Errors,
  defineField: defineStep3Field,
  resetForm: resetStep3Form,
  setErrors: setStep3Errors,
} = useForm({
  validationSchema: step3Schema,
  initialValues: getStep3InitialValues(),
  validateOnMount: false,
})

// Step 1 fields (Client)
const [lastName, lastNameAttrs] = defineStep1Field('lastName')
const [firstName, firstNameAttrs] = defineStep1Field('firstName')
const [email, emailAttrs] = defineStep1Field('email')
const [phone, phoneAttrs] = defineStep1Field('phone')

// Step 2 fields (Car basic)
const [manufacturer, manufacturerAttrs] = defineStep2Field('manufacturer')
const [model, modelAttrs] = defineStep2Field('model')
const [licensePlate, licensePlateAttrs] = defineStep2Field('licensePlate')
const [color, colorAttrs] = defineStep2Field('color')

// Step 3 fields (Car additional)
const [registrationYear, registrationYearAttrs] = defineStep3Field('registrationYear')
const [registrationMonth, registrationMonthAttrs] = defineStep3Field('registrationMonth')
const [mileage, mileageAttrs] = defineStep3Field('mileage')

// Fetch manufacturers
const { data: manufacturers, isLoading: isLoadingManufacturers } = useManufacturersQuery()

const manufacturerOptions = computed(() => {
  if (!manufacturers.value) return []
  return manufacturers.value.map((m) => ({
    value: m.name,
    label: m.name,
  }))
})

const { mutate: createCarWithClient, isLoading } = useCreateCarWithClientMutation(
  () => {
    emit('success')
    emit('close')
  },
  (error) => {
    hasAttemptedSubmit.value = true
    // Check if error is about color validation
    if (error.message && error.message.includes('Color not found')) {
      setStep2Errors({
        color: 'Couleur non disponible',
      })
      step.value = 2 // Go back to step 2 to show error
    }
  },
)

const dialogTitle = computed(() => {
  if (step.value === 1) return 'Créer une fiche voiture (1/3)'
  if (step.value === 2) return 'Créer une fiche voiture (2/3)'
  return 'Créer une fiche voiture (3/3)'
})

const dialogDescription = computed(() => {
  if (step.value === 1) return 'Renseignez les informations du client.'
  if (step.value === 2) return 'Renseignez les informations de base de la voiture.'
  return 'Renseignez les informations complémentaires (optionnel).'
})

function handleClose() {
  step.value = 1
  emit('close')
}

function handlePrevious() {
  if (step.value > 1) {
    step.value -= 1
  }
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
  () => {
    step.value = 3
  },
  () => {
    hasAttemptedSubmit.value = true
  },
)

const onStep3Submit = handleStep3Submit(
  (vals) => {
    createCarWithClient({
      client: {
        firstName: step1Values.firstName!,
        lastName: step1Values.lastName!,
        email: step1Values.email || null,
        phone: step1Values.phone!,
      },
      car: {
        manufacturer: step2Values.manufacturer!,
        model: step2Values.model!,
        licensePlate: step2Values.licensePlate!,
        color: step2Values.color!,
        registrationYear: typeof vals.registrationYear === 'number' ? vals.registrationYear : null,
        registrationMonth:
          typeof vals.registrationMonth === 'number' ? vals.registrationMonth : null,
        mileage: typeof vals.mileage === 'number' ? vals.mileage : null,
      },
    })
  },
  () => {
    hasAttemptedSubmit.value = true
  },
)

function formatLicensePlate(event: Event) {
  const input = event.target as HTMLInputElement
  let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '')

  if (value.length > 2 && value.length <= 5) {
    value = value.slice(0, 2) + '-' + value.slice(2)
  } else if (value.length > 5) {
    value = value.slice(0, 2) + '-' + value.slice(2, 5) + '-' + value.slice(5, 7)
  }

  licensePlate.value = value
}
</script>

<template>
  <Dialog :open="open" @update:open="(isOpen) => !isOpen && handleClose()">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ dialogTitle }}</DialogTitle>
        <DialogDescription>{{ dialogDescription }}</DialogDescription>
      </DialogHeader>

      <!-- Step 1 Form (Client) -->
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

        <!-- Email -->
        <FieldGroup class="gap-2">
          <FieldLabel for="email">Email (optionnel)</FieldLabel>
          <Field>
            <Input id="email" v-model="email" v-bind="emailAttrs" type="email" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.email">{{
              step1Errors.email
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Téléphone -->
        <FieldGroup class="gap-2">
          <FieldLabel for="phone">Téléphone</FieldLabel>
          <Field>
            <Input id="phone" v-model="phone" v-bind="phoneAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.phone">{{
              step1Errors.phone
            }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Annuler</Button>
          <Button type="submit">Suivant</Button>
        </DialogFooter>
      </form>

      <!-- Step 2 Form (Car) -->
      <form v-if="step === 2" @submit="onStep2Submit" class="grid gap-3 py-0">
        <!-- Marque -->
        <FieldGroup class="gap-2">
          <FieldLabel for="manufacturer">Marque</FieldLabel>
          <Field>
            <ManufacturerCombobox
              v-model="manufacturer"
              :options="manufacturerOptions"
              :loading="isLoadingManufacturers"
            />
            <FieldError v-if="hasAttemptedSubmit && step2Errors.manufacturer">{{
              step2Errors.manufacturer
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Modèle -->
        <FieldGroup class="gap-2">
          <FieldLabel for="model">Modèle</FieldLabel>
          <Field>
            <Input id="model" v-model="model" v-bind="modelAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && step2Errors.model">{{
              step2Errors.model
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Plaque d'immatriculation -->
        <FieldGroup class="gap-2">
          <FieldLabel for="licensePlate">Plaque d'immatriculation</FieldLabel>
          <Field>
            <Input
              id="licensePlate"
              v-model="licensePlate"
              v-bind="licensePlateAttrs"
              type="text"
              placeholder="AB-123-CD"
              maxlength="9"
              @input="formatLicensePlate"
            />
            <FieldError v-if="hasAttemptedSubmit && step2Errors.licensePlate">{{
              step2Errors.licensePlate
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Couleur -->
        <FieldGroup class="gap-2">
          <FieldLabel for="color">Couleur</FieldLabel>
          <Field>
            <Input id="color" v-model="color" v-bind="colorAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && step2Errors.color">{{
              step2Errors.color
            }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handlePrevious">Précédent</Button>
          <Button type="submit">Suivant</Button>
        </DialogFooter>
      </form>

      <!-- Step 3 Form (Car additional info) -->
      <form v-if="step === 3" @submit="onStep3Submit" class="grid gap-3 py-0">
        <!-- Année d'immatriculation -->
        <FieldGroup class="gap-2">
          <FieldLabel for="registrationYear">Année d'immatriculation (optionnel)</FieldLabel>
          <Field>
            <Input
              id="registrationYear"
              v-model="registrationYear"
              v-bind="registrationYearAttrs"
              type="number"
              min="1900"
              max="2100"
              placeholder="2020"
            />
            <FieldError v-if="hasAttemptedSubmit && step3Errors.registrationYear">{{
              step3Errors.registrationYear
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Mois d'immatriculation -->
        <FieldGroup class="gap-2">
          <FieldLabel for="registrationMonth">Mois d'immatriculation (optionnel)</FieldLabel>
          <Field>
            <Input
              id="registrationMonth"
              v-model="registrationMonth"
              v-bind="registrationMonthAttrs"
              type="number"
              min="1"
              max="12"
              placeholder="6"
            />
            <FieldError v-if="hasAttemptedSubmit && step3Errors.registrationMonth">{{
              step3Errors.registrationMonth
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Kilométrage -->
        <FieldGroup class="gap-2">
          <FieldLabel for="mileage">Kilométrage (optionnel)</FieldLabel>
          <Field>
            <Input
              id="mileage"
              v-model="mileage"
              v-bind="mileageAttrs"
              type="number"
              min="0"
              placeholder="50000"
            />
            <FieldError v-if="hasAttemptedSubmit && step3Errors.mileage">{{
              step3Errors.mileage
            }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handlePrevious">Précédent</Button>
          <Button type="submit" :disabled="isLoading">
            {{ isLoading ? 'Création...' : 'Créer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
