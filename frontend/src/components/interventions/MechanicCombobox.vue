<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
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

interface MechanicOption {
  value: number
  label: string
}

interface Props {
  modelValue: number | undefined
  options: MechanicOption[]
  loading?: boolean
  placeholder?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  placeholder: 'Sélectionnez un mécanicien',
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: number | undefined]
}>()

const comboboxOpen = ref(false)

const selectedLabel = computed(() => {
  if (!props.modelValue) return props.placeholder
  const selected = props.options.find((opt) => opt.value === props.modelValue)
  return selected ? selected.label : props.placeholder
})

function selectMechanic(id: number) {
  emit('update:modelValue', id)
  comboboxOpen.value = false
}
</script>

<template>
  <Popover v-model:open="comboboxOpen">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        role="combobox"
        :aria-expanded="comboboxOpen"
        :disabled="disabled"
        class="w-96 justify-between"
      >
        {{ selectedLabel }}
        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-112 p-0">
      <Command>
        <CommandInput placeholder="Rechercher un mécanicien..." />
        <CommandList>
          <CommandEmpty v-if="!loading">Aucun mécanicien trouvé.</CommandEmpty>
          <CommandEmpty v-else>Chargement...</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="option in options"
              :key="option.value"
              :value="option.label"
              @select="selectMechanic(option.value)"
            >
              <Check
                :class="
                  cn(
                    'mr-2 h-4 w-4',
                    modelValue === option.value ? 'opacity-100' : 'opacity-0',
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
</template>
