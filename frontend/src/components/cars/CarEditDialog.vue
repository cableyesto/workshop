<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { useUpdateCarMutation } from '../../api/cars'
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
import type { Car } from '../../types/cars'

interface Props {
  open: boolean
  car: Car | null
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
      if (props.car) {
        setStep1Values({
          manufacturer: props.car.manufacturer,
          model: props.car.model,
          licensePlate: props.car.licensePlate,
          color: props.car.color,
        })
        setStep2Values({
          registrationYear: props.car.registrationYear !== null ? props.car.registrationYear : '',
          registrationMonth: props.car.registrationMonth !== null ? props.car.registrationMonth : '',
          mileage: props.car.mileage !== null ? props.car.mileage : '',
        })
      } else {
        resetStep1Form()
        resetStep2Form()
      }
    }
  },
)

// Step 1 validation schema (Car basic info)
const step1Schema = toTypedSchema(
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

// Step 2 validation schema (Car additional info)
const step2Schema = toTypedSchema(
  z.object({
    registrationYear: z
      .union([z.coerce.number().int().min(1900).max(2100), z.literal('')])
      .optional(),
    registrationMonth: z.union([z.coerce.number().int().min(1).max(12), z.literal('')]).optional(),
    mileage: z.union([z.coerce.number().int().min(0), z.literal('')]).optional(),
  }),
)

const getStep1InitialValues = () => {
  if (props.car) {
    return {
      manufacturer: props.car.manufacturer,
      model: props.car.model,
      licensePlate: props.car.licensePlate,
      color: props.car.color,
    }
  }
  return {
    manufacturer: '',
    model: '',
    licensePlate: '',
    color: '',
  }
}

const getStep2InitialValues = () => {
  if (props.car) {
    return {
      registrationYear: props.car.registrationYear !== null ? props.car.registrationYear : ('' as const),
      registrationMonth: props.car.registrationMonth !== null ? props.car.registrationMonth : ('' as const),
      mileage: props.car.mileage !== null ? props.car.mileage : ('' as const),
    }
  }
  return {
    registrationYear: '' as const,
    registrationMonth: '' as const,
    mileage: '' as const,
  }
}

const {
  handleSubmit: handleStep1Submit,
  values: step1Values,
  errors: step1Errors,
  defineField: defineStep1Field,
  resetForm: resetStep1Form,
  setValues: setStep1Values,
  setErrors: setStep1Errors,
} = useForm({
  validationSchema: step1Schema,
  initialValues: getStep1InitialValues(),
  validateOnMount: false,
})

const {
  handleSubmit: handleStep2Submit,
  errors: step2Errors,
  defineField: defineStep2Field,
  resetForm: resetStep2Form,
  setValues: setStep2Values,
} = useForm({
  validationSchema: step2Schema,
  initialValues: getStep2InitialValues(),
  validateOnMount: false,
})

// Step 1 fields (Car basic)
const [manufacturer, manufacturerAttrs] = defineStep1Field('manufacturer')
const [model, modelAttrs] = defineStep1Field('model')
const [licensePlate, licensePlateAttrs] = defineStep1Field('licensePlate')
const [color, colorAttrs] = defineStep1Field('color')

// Step 2 fields (Car additional)
const [registrationYear, registrationYearAttrs] = defineStep2Field('registrationYear')
const [registrationMonth, registrationMonthAttrs] = defineStep2Field('registrationMonth')
const [mileage, mileageAttrs] = defineStep2Field('mileage')

const { mutate: updateCar, isLoading } = useUpdateCarMutation(
  () => {
    emit('success')
    emit('close')
  },
  (error) => {
    hasAttemptedSubmit.value = true
    // Check if error is about color validation
    if (error.message && error.message.includes('Color not found')) {
      setStep1Errors({
        color: 'Couleur non disponible',
      })
      step.value = 1 // Go back to step 1 to show error
    }
  },
)

const dialogTitle = computed(() => {
  if (step.value === 1) return 'Modifier la fiche voiture (1/2)'
  return 'Modifier la fiche voiture (2/2)'
})

const dialogDescription = computed(() => {
  if (step.value === 1) return 'Renseignez les informations de base de la voiture.'
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
  (vals) => {
    if (!props.car) return

    updateCar({
      carId: props.car.id,
      data: {
        manufacturer: step1Values.manufacturer!,
        model: step1Values.model!,
        licensePlate: step1Values.licensePlate!,
        color: step1Values.color!,
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

      <!-- Step 1 Form (Car basic info) -->
      <form v-if="step === 1" @submit="onStep1Submit" class="grid gap-3 py-0">
        <!-- Marque -->
        <FieldGroup class="gap-2">
          <FieldLabel for="manufacturer">Marque</FieldLabel>
          <Field>
            <Input
              id="manufacturer"
              v-model="manufacturer"
              v-bind="manufacturerAttrs"
              type="text"
            />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.manufacturer">{{
              step1Errors.manufacturer
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Modèle -->
        <FieldGroup class="gap-2">
          <FieldLabel for="model">Modèle</FieldLabel>
          <Field>
            <Input id="model" v-model="model" v-bind="modelAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.model">{{
              step1Errors.model
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
            <FieldError v-if="hasAttemptedSubmit && step1Errors.licensePlate">{{
              step1Errors.licensePlate
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Couleur -->
        <FieldGroup class="gap-2">
          <FieldLabel for="color">Couleur</FieldLabel>
          <Field>
            <Input id="color" v-model="color" v-bind="colorAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && step1Errors.color">{{
              step1Errors.color
            }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Annuler</Button>
          <Button type="submit">Suivant</Button>
        </DialogFooter>
      </form>

      <!-- Step 2 Form (Car additional info) -->
      <form v-if="step === 2" @submit="onStep2Submit" class="grid gap-3 py-0">
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
            <FieldError v-if="hasAttemptedSubmit && step2Errors.registrationYear">{{
              step2Errors.registrationYear
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
            <FieldError v-if="hasAttemptedSubmit && step2Errors.registrationMonth">{{
              step2Errors.registrationMonth
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
            <FieldError v-if="hasAttemptedSubmit && step2Errors.mileage">{{
              step2Errors.mileage
            }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handlePrevious">Précédent</Button>
          <Button type="submit" :disabled="isLoading">
            {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
