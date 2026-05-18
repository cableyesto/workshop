<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import type { Act, ActPayload } from '@/types/act'

interface Props {
  interventionId: number | undefined
  isEditMode: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  next: []
  back: []
  cancel: []
}>()

// Local state for acts
const acts = ref<Act[]>([])
const showActDialog = ref(false)
const editingAct = ref<Act | null>(null)

function handleAddAct() {
  editingAct.value = null
  showActDialog.value = true
}

function handleEditAct(act: Act) {
  editingAct.value = act
  showActDialog.value = true
}

function handleSaveAct(payload: ActPayload) {
  if (editingAct.value) {
    // Edit existing act
    const index = acts.value.findIndex((a) => a.id === editingAct.value!.id)
    if (index !== -1) {
      acts.value[index] = { ...payload, id: editingAct.value.id }
    }
  } else {
    // Add new act (temporary ID until backend save)
    const newAct: Act = {
      ...payload,
      id: Date.now(),
    }
    acts.value.push(newAct)
  }
  showActDialog.value = false
}

function handleCancelDialog() {
  showActDialog.value = false
  editingAct.value = null
}

function handleValidate() {
  // TODO: Save acts to backend
  emit('next')
}
</script>

<template>
  <div class="grid gap-6">
    <h2 class="text-2xl font-semibold">Étape 4: Actes</h2>

    <!-- Breadcrumb -->
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem>
          <span class="text-muted-foreground">Type</span>
        </BreadcrumbItem>
        <BreadcrumbSeparator />
        <BreadcrumbItem>
          <span class="text-muted-foreground">Remarque</span>
        </BreadcrumbItem>
        <BreadcrumbSeparator />
        <BreadcrumbItem>
          <BreadcrumbPage>Acte</BreadcrumbPage>
        </BreadcrumbItem>
      </BreadcrumbList>
    </Breadcrumb>

    <!-- Add Act Button -->
    <div>
      <Button @click="handleAddAct">Ajouter un acte</Button>
    </div>

    <!-- Acts Table -->
    <div class="border rounded-lg">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Acte</TableHead>
            <TableHead>Quantité</TableHead>
            <TableHead>Prix unitaire</TableHead>
            <TableHead>Editer</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="acts.length === 0">
            <TableCell colspan="4" class="text-center text-muted-foreground py-8">
              Aucun acte ajouté
            </TableCell>
          </TableRow>
          <TableRow v-for="act in acts" :key="act.id">
            <TableCell>{{ act.name }}</TableCell>
            <TableCell>{{ act.quantity }}</TableCell>
            <TableCell>{{ act.unitPrice.toFixed(2) }}</TableCell>
            <TableCell>
              <button
                class="text-blue-600 hover:underline cursor-pointer"
                @click="handleEditAct(act)"
              >
                Modification
              </button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-2 gap-6">
      <Button type="button" variant="outline" @click="$emit('back')">Précédent</Button>
      <Button type="button" @click="handleValidate">Valider</Button>
    </div>

    <!-- TODO: Add ActDialog component here -->
  </div>
</template>
