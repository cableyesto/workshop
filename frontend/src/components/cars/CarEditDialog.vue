<script setup lang="ts">
import { ref, watch } from 'vue'
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

const hasAttemptedSubmit = ref(false)

// Watch dialog open/close
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      hasAttemptedSubmit.value = false
      if (props.car) {
        setValues({
          manufacturer: props.car.manufacturer,
          model: props.car.model,
          licensePlate: props.car.licensePlate,
          color: props.car.color,
          registrationYear: props.car.registrationYear ?? '',
          registrationMonth: props.car.registrationMonth ?? '',
          mileage: props.car.mileage ?? '',
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
    manufacturer: z.string().min(1, 'La marque est requise'),
    model: z.string().min(1, 'Le modèle est requis'),
    licensePlate: z
      .string()
      .length(9, 'La plaque doit contenir exactement 9 caractères')
      .regex(/^[A-Z]{2}-\d{3}-[A-Z]{2}$/, 'Format invalide. Attendu: AB-123-CD'),
    color: z.string().min(1, 'La couleur est requise'),
    registrationYear: z.coerce.number().int().min(1900).max(2100).nullable().optional().or(z.literal('')),
    registrationMonth: z.coerce.number().int().min(1).max(12).nullable().optional().or(z.literal('')),
    mileage: z.coerce.number().int().min(0).nullable().optional().or(z.literal('')),
  }),
)

const getInitialValues = () => {
  if (props.car) {
    return {
      manufacturer: props.car.manufacturer,
      model: props.car.model,
      licensePlate: props.car.licensePlate,
      color: props.car.color,
      registrationYear: props.car.registrationYear ?? '',
      registrationMonth: props.car.registrationMonth ?? '',
      mileage: props.car.mileage ?? '',
    }
  }
  return {
    manufacturer: '',
    model: '',
    licensePlate: '',
    color: '',
    registrationYear: '',
    registrationMonth: '',
    mileage: '',
  }
}

const {
  handleSubmit,
  errors,
  defineField,
  resetForm,
  setValues,
} = useForm({
  validationSchema: schema,
  initialValues: getInitialValues(),
  validateOnMount: false,
})

// Fields
const [manufacturer, manufacturerAttrs] = defineField('manufacturer')
const [model, modelAttrs] = defineField('model')
const [licensePlate, licensePlateAttrs] = defineField('licensePlate')
const [color, colorAttrs] = defineField('color')
const [registrationYear, registrationYearAttrs] = defineField('registrationYear')
const [registrationMonth, registrationMonthAttrs] = defineField('registrationMonth')
const [mileage, mileageAttrs] = defineField('mileage')

const { mutate: updateCar, isPending } = useUpdateCarMutation(
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
    if (!props.car) return

    updateCar({
      carId: props.car.id,
      data: {
        manufacturer: vals.manufacturer,
        model: vals.model,
        licensePlate: vals.licensePlate,
        color: vals.color,
        registrationYear: typeof vals.registrationYear === 'number' ? vals.registrationYear : null,
        registrationMonth: typeof vals.registrationMonth === 'number' ? vals.registrationMonth : null,
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
        <DialogTitle>Modifier la fiche voiture</DialogTitle>
        <DialogDescription>Modifiez les informations de la voiture</DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit" class="grid gap-3 py-0">
        <!-- Marque -->
        <FieldGroup class="gap-2">
          <FieldLabel for="manufacturer">Marque</FieldLabel>
          <Field>
            <Input id="manufacturer" v-model="manufacturer" v-bind="manufacturerAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && errors.manufacturer">{{
              errors.manufacturer
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Modèle -->
        <FieldGroup class="gap-2">
          <FieldLabel for="model">Modèle</FieldLabel>
          <Field>
            <Input id="model" v-model="model" v-bind="modelAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && errors.model">{{ errors.model }}</FieldError>
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
            <FieldError v-if="hasAttemptedSubmit && errors.licensePlate">{{
              errors.licensePlate
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Couleur -->
        <FieldGroup class="gap-2">
          <FieldLabel for="color">Couleur</FieldLabel>
          <Field>
            <Input id="color" v-model="color" v-bind="colorAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && errors.color">{{ errors.color }}</FieldError>
          </Field>
        </FieldGroup>

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
            <FieldError v-if="hasAttemptedSubmit && errors.registrationYear">{{
              errors.registrationYear
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
            <FieldError v-if="hasAttemptedSubmit && errors.registrationMonth">{{
              errors.registrationMonth
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
            <FieldError v-if="hasAttemptedSubmit && errors.mileage">{{
              errors.mileage
            }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Annuler</Button>
          <Button type="submit" :disabled="isPending">
            {{ isPending ? 'Enregistrement...' : 'Enregistrer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
