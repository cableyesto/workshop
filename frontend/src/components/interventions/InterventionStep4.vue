<script setup lang="ts">
import { ref, watch, computed } from 'vue'
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
import { useInterventionTasksQuery } from '@/api/interventions'
import TaskDialog from './TaskDialog.vue'
import type { Task, TaskPayload } from '@/types/task'

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

// Fetch tasks data (cached, only in edit mode)
const { data: tasksData } = useInterventionTasksQuery(props.interventionId, props.isEditMode)

// Local state for tasks
const tasks = ref<Task[]>([])
const showTaskDialog = ref(false)
const editingTask = ref<Task | null>(null)

// Dialog mode
const dialogMode = computed(() => (editingTask.value ? 'edit' : 'create'))

// Watch for tasks data and populate table
watch(
  tasksData,
  (data) => {
    if (data && props.isEditMode) {
      tasks.value = [...data]
    }
  },
  { immediate: true },
)

function handleAddTask() {
  editingTask.value = null
  showTaskDialog.value = true
}

function handleEditTask(task: Task) {
  editingTask.value = task
  showTaskDialog.value = true
}

function handleSaveTask(payload: TaskPayload) {
  if (!props.interventionId) {
    alert('Erreur: ID intervention manquant')
    return
  }

  if (editingTask.value) {
    // TODO: Call PATCH API to update task on backend
    console.log('Update task:', editingTask.value.id, payload)
    // For now, update locally
    const index = tasks.value.findIndex((t) => t.id === editingTask.value!.id)
    if (index !== -1) {
      tasks.value[index] = { ...payload, id: editingTask.value.id }
    }
  } else {
    // TODO: Call POST API to create task on backend
    console.log('Create task for intervention:', props.interventionId, payload)
    // For now, add locally with temporary ID
    const newTask: Task = {
      ...payload,
      id: Date.now(),
    }
    tasks.value.push(newTask)
  }
  showTaskDialog.value = false
  editingTask.value = null
}

function handleCloseDialog() {
  showTaskDialog.value = false
  editingTask.value = null
}

function handleValidate() {
  // TODO: Save tasks to backend
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

    <!-- Add Task Button -->
    <div>
      <Button @click="handleAddTask">Ajouter un acte</Button>
    </div>

    <!-- Tasks Table -->
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
          <TableRow v-if="tasks.length === 0">
            <TableCell colspan="4" class="text-center text-muted-foreground py-8">
              Aucun acte ajouté
            </TableCell>
          </TableRow>
          <TableRow v-for="task in tasks" :key="task.id">
            <TableCell>{{ task.name }}</TableCell>
            <TableCell>{{ task.quantity }}</TableCell>
            <TableCell>{{ task.unitPrice.toFixed(2) }}</TableCell>
            <TableCell>
              <button
                class="text-blue-600 hover:underline cursor-pointer"
                @click="handleEditTask(task)"
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

    <!-- Task Dialog -->
    <TaskDialog
      :open="showTaskDialog"
      :mode="dialogMode"
      :task="editingTask"
      @submit="handleSaveTask"
      @close="handleCloseDialog"
    />
  </div>
</template>
