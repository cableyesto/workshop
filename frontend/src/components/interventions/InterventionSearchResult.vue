<script setup lang="ts">
import type { SearchInterventionResponse } from '../../types/intervention'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'

interface Props {
  open: boolean
  result: SearchInterventionResponse | null
}

defineProps<Props>()

const emit = defineEmits<{
  create: []
  edit: [interventionId: number]
  cancel: []
}>()
</script>

<template>
  <AlertDialog :open="open">
    <AlertDialogContent v-if="result">
      <!-- Found: Intervention exists -->
      <template v-if="result.found && result.intervention">
        <AlertDialogHeader>
          <AlertDialogTitle>⚠️ Intervention existante</AlertDialogTitle>
          <AlertDialogDescription>
            Une intervention en cours existe déjà pour ce mécanicien et cette voiture.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div class="py-4 space-y-2">
          <div>
            <span class="font-semibold">Statut:</span>
            {{ result.intervention.status }}
          </div>
          <div v-if="result.intervention.startDate">
            <span class="font-semibold">Créée le:</span>
            {{ result.intervention.startDate }}
            <span v-if="result.intervention.startTime">à {{ result.intervention.startTime }}</span>
          </div>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel @click="$emit('cancel')">Annuler</AlertDialogCancel>
          <AlertDialogAction @click="$emit('edit', result.intervention!.id)">
            Modifier l'intervention
          </AlertDialogAction>
        </AlertDialogFooter>
      </template>

      <!-- Not Found: Create new -->
      <template v-else-if="!result.found">
        <AlertDialogHeader>
          <AlertDialogTitle>✓ Aucune intervention en cours</AlertDialogTitle>
          <AlertDialogDescription>
            Aucune intervention en cours trouvée pour cette combinaison.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <AlertDialogFooter>
          <AlertDialogCancel @click="$emit('cancel')">Annuler</AlertDialogCancel>
          <AlertDialogAction @click="$emit('create')">Créer le processus</AlertDialogAction>
        </AlertDialogFooter>
      </template>
    </AlertDialogContent>
  </AlertDialog>
</template>
