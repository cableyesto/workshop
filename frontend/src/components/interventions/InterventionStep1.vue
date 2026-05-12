<script setup lang="ts">
import { ref, computed } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { useMechanicsQuery } from '../../api/employees'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Check, ChevronsUpDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Props {
  mechanicId: number | undefined
  licensePlate: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:mechanicId': [value: number | undefined]
  'update:licensePlate': [value: string]
  search: []
}>()

const hasAttemptedSubmit = ref(false)
const mechanicComboboxOpen = ref(false)

// Fetch mechanics
const { data: mechanics, isLoading: mechanicsLoading } = useMechanicsQuery()

// Computed mechanic options for combobox
const mechanicOptions = computed(() => {
  if (!mechanics.value) return []
  return mechanics.value.map((m) => ({
    value: m.id,
    label: `${m.firstName} ${m.lastName}`,
  }))
})

// Selected mechanic label
const selectedMechanicLabel = computed(() => {
  if (!props.mechanicId) return 'Sélectionnez un mécanicien'
  const mechanic = mechanics.value?.find((m) => m.id === props.mechanicId)
  return mechanic ? `${mechanic.firstName} ${mechanic.lastName}` : 'Sélectionnez un mécanicien'
})

// Validation schema
const schema = toTypedSchema(
  z.object({
    mechanicId: z.number({ required_error: 'Sélectionnez un mécanicien' }),
    licensePlate: z
      .string()
      .min(1, 'La plaque est requise')
      .refine(
        (val) => val.length === 0 || val.length === 9,
        'La plaque doit contenir exactement 9 caractères',
      )
      .refine(
        (val) => val.length === 0 || /^[A-Z]{2}-\d{3}-[A-Z]{2}$/.test(val),
        'Format invalide. Attendu: AB-123-CD',
      ),
  }),
)

const { handleSubmit, errors, defineField } = useForm({
  validationSchema: schema,
  initialValues: {
    mechanicId: props.mechanicId,
    licensePlate: props.licensePlate,
  },
})

const [mechanicId] = defineField('mechanicId')
const [licensePlate, licensePlateAttrs] = defineField('licensePlate')

const onSubmit = handleSubmit(
  () => {
    emit('search')
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

  emit('update:licensePlate', value)
}

function selectMechanic(id: number) {
  mechanicId.value = id
  emit('update:mechanicId', id)
  mechanicComboboxOpen.value = false
}
</script>

<template>
  <form @submit="onSubmit" class="grid gap-6">
    <!-- Mechanic ComboBox -->
    <FieldGroup class="gap-2">
      <FieldLabel for="mechanic">Mécanicien</FieldLabel>
      <Field>
        <Popover v-model:open="mechanicComboboxOpen">
          <PopoverTrigger as-child>
            <Button
              variant="outline"
              role="combobox"
              :aria-expanded="mechanicComboboxOpen"
              class="w-96 justify-between"
            >
              {{ selectedMechanicLabel }}
              <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
          </PopoverTrigger>
          <!-- <PopoverContent class="w-96 p-0"> -->
          <PopoverContent class="w-112 p-0">
            <Command>
              <CommandInput placeholder="Rechercher un mécanicien..." />
              <CommandList>
                <CommandEmpty v-if="!mechanicsLoading">Aucun mécanicien trouvé.</CommandEmpty>
                <CommandEmpty v-else>Chargement...</CommandEmpty>
                <CommandGroup>
                  <CommandItem
                    v-for="option in mechanicOptions"
                    :key="option.value"
                    :value="option.label"
                    @select="selectMechanic(option.value)"
                  >
                    <Check
                      :class="
                        cn(
                          'mr-2 h-4 w-4',
                          mechanicId === option.value ? 'opacity-100' : 'opacity-0',
                        )
                      "
                    />
                    {{ option.label }}
                  </CommandItem>
                </CommandGroup>
              </CommandList>
            </Command>
          </PopoverContent>
        </Popover>
        <FieldError v-if="hasAttemptedSubmit && errors.mechanicId">{{
          errors.mechanicId
        }}</FieldError>
      </Field>
    </FieldGroup>

    <!-- License Plate -->
    <FieldGroup class="gap-2">
      <FieldLabel for="licensePlate">Immatriculation voiture</FieldLabel>
      <Field>
        <Input
          id="licensePlate"
          :model-value="licensePlate"
          v-bind="licensePlateAttrs"
          type="text"
          placeholder="AB-123-CD"
          maxlength="9"
          class="w-96"
          @input="formatLicensePlate"
        />
        <FieldError v-if="hasAttemptedSubmit && errors.licensePlate">{{
          errors.licensePlate
        }}</FieldError>
      </Field>
    </FieldGroup>

    <Button type="submit">Rechercher</Button>
  </form>
</template>
