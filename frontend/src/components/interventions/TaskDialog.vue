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
import type { Task, TaskPayload } from '@/types/task'

interface Props {
  open: boolean
  mode: 'create' | 'edit'
  task?: Task | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  submit: [data: TaskPayload]
}>()

const hasAttemptedSubmit = ref(false)

// Watch dialog open/close
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      hasAttemptedSubmit.value = false
      if (props.mode === 'edit' && props.task) {
        setValues({
          name: props.task.name,
          quantity: props.task.quantity,
          unitPrice: props.task.unitPrice,
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
    name: z.string().min(1, 'Nom requis').max(255, 'Le nom ne peut pas dépasser 255 caractères'),
    quantity: z
      .number({ required_error: 'Quantité requise' })
      .int('La quantité doit être un nombre entier')
      .min(1, 'La quantité doit être au moins 1'),
    unitPrice: z
      .number({ required_error: 'Prix unitaire requis' })
      .min(0, 'Le prix ne peut pas être négatif'),
  }),
)

const getInitialValues = () => {
  if (props.mode === 'edit' && props.task) {
    return {
      name: props.task.name,
      quantity: props.task.quantity,
      unitPrice: props.task.unitPrice,
    }
  }
  return {
    name: '',
    quantity: 1,
    unitPrice: 0,
  }
}

const { handleSubmit, errors, defineField, resetForm, setValues } = useForm({
  validationSchema: schema,
  initialValues: getInitialValues(),
  validateOnMount: false,
})

// Fields
const [name, nameAttrs] = defineField('name')
const [quantity, quantityAttrs] = defineField('quantity')
const [unitPrice, unitPriceAttrs] = defineField('unitPrice')

const dialogTitle = computed(() => {
  const action = props.mode === 'edit' ? 'Modifier' : 'Ajouter'
  return `${action} un acte`
})

const dialogDescription = computed(() => {
  if (props.mode === 'edit') {
    return "Modifiez les informations de l'acte."
  }
  return 'Remplissez les informations du nouvel acte.'
})

function handleClose() {
  emit('close')
}

const onSubmit = handleSubmit(
  (vals) => {
    emit('submit', {
      name: vals.name,
      quantity: vals.quantity,
      unitPrice: vals.unitPrice,
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
          <FieldLabel for="name">Nom de l'acte</FieldLabel>
          <Field>
            <Input id="name" v-model="name" v-bind="nameAttrs" type="text" />
            <FieldError v-if="hasAttemptedSubmit && errors.name">{{ errors.name }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Quantité -->
        <FieldGroup class="gap-2">
          <FieldLabel for="quantity">Quantité</FieldLabel>
          <Field>
            <Input
              id="quantity"
              v-model.number="quantity"
              v-bind="quantityAttrs"
              type="number"
              min="1"
              step="1"
            />
            <FieldError v-if="hasAttemptedSubmit && errors.quantity">{{
              errors.quantity
            }}</FieldError>
          </Field>
        </FieldGroup>

        <!-- Prix unitaire -->
        <FieldGroup class="gap-2">
          <FieldLabel for="unitPrice">Prix unitaire (€)</FieldLabel>
          <Field>
            <Input
              id="unitPrice"
              v-model.number="unitPrice"
              v-bind="unitPriceAttrs"
              type="number"
              min="0"
              step="0.5"
            />
            <FieldError v-if="hasAttemptedSubmit && errors.unitPrice">{{
              errors.unitPrice
            }}</FieldError>
          </Field>
        </FieldGroup>

        <DialogFooter class="flex justify-end gap-2">
          <Button type="button" variant="outline" @click="handleClose">Annuler</Button>
          <Button type="submit">{{ mode === 'edit' ? 'Modifier' : 'Ajouter' }}</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
