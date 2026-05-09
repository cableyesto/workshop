<script setup lang="ts">
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
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import type { Client } from '../../types/cars'

interface Props {
  open: boolean
  client: Client | null
}

defineProps<Props>()

const emit = defineEmits<{
  close: []
}>()
</script>

<template>
  <Dialog :open="open" @update:open="(val) => !val && emit('close')">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>Informations du client</DialogTitle>
        <DialogDescription>Détails du client propriétaire du véhicule</DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-0">
        <Field>
          <FieldGroup class="gap-2">
            <FieldLabel>Nom</FieldLabel>
            <Input :model-value="client?.lastName || ''" disabled />
          </FieldGroup>
        </Field>

        <Field>
          <FieldGroup class="gap-2">
            <FieldLabel>Prénom</FieldLabel>
            <Input :model-value="client?.firstName || ''" disabled />
          </FieldGroup>
        </Field>

        <Field>
          <FieldGroup class="gap-2">
            <FieldLabel>Email</FieldLabel>
            <Input :model-value="client?.email || ''" disabled />
          </FieldGroup>
        </Field>

        <Field>
          <FieldGroup class="gap-2">
            <FieldLabel>Téléphone</FieldLabel>
            <Input :model-value="client?.phone || 'Non renseigné'" disabled />
          </FieldGroup>
        </Field>
      </div>

      <DialogFooter>
        <Button @click="emit('close')" variant="outline"> Fermer </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
