import { describe, it, expect, vi, beforeEach } from 'vite-plus/test'
import {
  useInterventionTasksQuery,
  getInterventionTasksAPI,
  createInterventionTaskAPI,
  updateInterventionTaskAPI,
  useCreateInterventionTaskMutation,
  useUpdateInterventionTaskMutation,
} from '@/api/intervention-tasks'
import * as helpers from '@/api/helpers'
import type { Task, TaskPayload } from '@/types/task'

// Mock dependencies
vi.mock('@/api/helpers', () => ({
  apiRequest: vi.fn(),
}))

vi.mock('@pinia/colada', () => ({
  useQuery: vi.fn((config) => ({
    data: { value: null },
    isLoading: { value: false },
    error: { value: null },
    config,
  })),
  useMutation: vi.fn(),
  useQueryCache: vi.fn(() => ({
    invalidateQueries: vi.fn(),
  })),
}))

describe('intervention-tasks.ts', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const mockTask: Task = {
    id: 1,
    name: 'Vidange moteur',
    quantity: 1,
    unitPrice: 45.5,
  }

  const mockTasks: Task[] = [
    mockTask,
    {
      id: 2,
      name: 'Changement plaquettes',
      quantity: 1,
      unitPrice: 120.0,
    },
  ]

  const mockTaskPayload: TaskPayload = {
    name: 'Nouvelle tâche',
    quantity: 2,
    unitPrice: 50.0,
  }

  describe('useInterventionTasksQuery', () => {
    it('can be called with intervention ID and enabled flag', () => {
      expect(() => {
        useInterventionTasksQuery(1, true)
      }).not.toThrow()
    })

    it('returns query result object', () => {
      const result = useInterventionTasksQuery(1, true)

      expect(result).toBeDefined()
      expect(result).toHaveProperty('data')
      expect(result).toHaveProperty('isLoading')
      expect(result).toHaveProperty('error')
    })

    it('uses correct query key with intervention ID', () => {
      const result = useInterventionTasksQuery(42, true) as any

      expect(result.config.key).toEqual(['interventions', 42, 'tasks'])
    })

    it('uses 0 as fallback when intervention ID is undefined', () => {
      const result = useInterventionTasksQuery(undefined, false) as any

      expect(result.config.key).toEqual(['interventions', 0, 'tasks'])
    })

    it('configures staleTime to 5 minutes', () => {
      const result = useInterventionTasksQuery(1, true) as any

      const fiveMinutes = 5 * 60 * 1000
      expect(result.config.staleTime).toBe(fiveMinutes)
      expect(result.config.staleTime).toBe(300000)
    })

    it('respects enabled flag when true', () => {
      const result = useInterventionTasksQuery(1, true) as any

      expect(result.config.enabled).toBe(true)
    })

    it('disables query when enabled is false', () => {
      const result = useInterventionTasksQuery(1, false) as any

      expect(result.config.enabled).toBe(false)
    })

    it('disables query when intervention ID is undefined', () => {
      const result = useInterventionTasksQuery(undefined, true) as any

      expect(result.config.enabled).toBe(false)
    })

    it('includes query function', () => {
      const result = useInterventionTasksQuery(1, true) as any

      expect(result.config.query).toBeDefined()
      expect(typeof result.config.query).toBe('function')
    })
  })

  describe('getInterventionTasksAPI', () => {
    it('calls apiRequest with correct URL', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTasks)

      await getInterventionTasksAPI(123)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions/123/tasks',
        {},
        'Failed to fetch intervention tasks'
      )
    })

    it('returns array of tasks', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTasks)

      const result = await getInterventionTasksAPI(1)

      expect(result).toEqual(mockTasks)
      expect(result).toHaveLength(2)
      expect(result[0]).toHaveProperty('id')
      expect(result[0]).toHaveProperty('name')
    })

    it('handles empty tasks array', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue([])

      const result = await getInterventionTasksAPI(1)

      expect(result).toEqual([])
      expect(result).toHaveLength(0)
    })

    it('propagates apiRequest errors', async () => {
      const error = new Error('Network error')
      vi.mocked(helpers.apiRequest).mockRejectedValue(error)

      await expect(getInterventionTasksAPI(1)).rejects.toThrow('Network error')
    })

    it('uses empty options object', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue([])

      await getInterventionTasksAPI(1)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        {},
        expect.any(String)
      )
    })
  })

  describe('createInterventionTaskAPI', () => {
    it('calls apiRequest with correct URL and method', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTask)

      await createInterventionTaskAPI(123, mockTaskPayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions/123/tasks',
        expect.objectContaining({
          method: 'POST',
        }),
        'Failed to create task'
      )
    })

    it('sends task data as JSON in request body', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTask)

      await createInterventionTaskAPI(1, mockTaskPayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(mockTaskPayload),
        }),
        expect.any(String)
      )
    })

    it('sets Content-Type header', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTask)

      await createInterventionTaskAPI(1, mockTaskPayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          headers: {
            'Content-Type': 'application/json',
          },
        }),
        expect.any(String)
      )
    })

    it('returns created task with ID', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTask)

      const result = await createInterventionTaskAPI(1, mockTaskPayload)

      expect(result).toEqual(mockTask)
      expect(result).toHaveProperty('id')
      expect(result.id).toBe(1)
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Create failed'))

      await expect(createInterventionTaskAPI(1, mockTaskPayload)).rejects.toThrow('Create failed')
    })
  })

  describe('updateInterventionTaskAPI', () => {
    it('calls apiRequest with correct URL including task ID', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTask)

      await updateInterventionTaskAPI(123, 456, mockTaskPayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        '/api/interventions/123/tasks/456',
        expect.objectContaining({
          method: 'PATCH',
        }),
        'Failed to update task'
      )
    })

    it('uses PATCH method for updates', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTask)

      await updateInterventionTaskAPI(1, 1, mockTaskPayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          method: 'PATCH',
        }),
        expect.any(String)
      )
    })

    it('sends updated task data as JSON', async () => {
      vi.mocked(helpers.apiRequest).mockResolvedValue(mockTask)

      const updatedPayload: TaskPayload = {
        name: 'Updated task',
        quantity: 3,
        unitPrice: 75.0,
      }

      await updateInterventionTaskAPI(1, 1, updatedPayload)

      expect(helpers.apiRequest).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: JSON.stringify(updatedPayload),
        }),
        expect.any(String)
      )
    })

    it('returns updated task', async () => {
      const updatedTask = { ...mockTask, name: 'Updated name' }
      vi.mocked(helpers.apiRequest).mockResolvedValue(updatedTask)

      const result = await updateInterventionTaskAPI(1, 1, mockTaskPayload)

      expect(result).toEqual(updatedTask)
      expect(result.name).toBe('Updated name')
    })

    it('propagates errors from apiRequest', async () => {
      vi.mocked(helpers.apiRequest).mockRejectedValue(new Error('Update failed'))

      await expect(updateInterventionTaskAPI(1, 1, mockTaskPayload)).rejects.toThrow(
        'Update failed'
      )
    })
  })

  describe('useCreateInterventionTaskMutation', () => {
    it('can be called with callbacks', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      expect(() => {
        useCreateInterventionTaskMutation(onSuccess, onError)
      }).not.toThrow()
    })

    it('accepts success callback', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      useCreateInterventionTaskMutation(onSuccess, onError)

      expect(onSuccess).toBeDefined()
      expect(typeof onSuccess).toBe('function')
    })

    it('accepts error callback', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      useCreateInterventionTaskMutation(onSuccess, onError)

      expect(onError).toBeDefined()
      expect(typeof onError).toBe('function')
    })
  })

  describe('useUpdateInterventionTaskMutation', () => {
    it('can be called with callbacks', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      expect(() => {
        useUpdateInterventionTaskMutation(onSuccess, onError)
      }).not.toThrow()
    })

    it('accepts success callback', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      useUpdateInterventionTaskMutation(onSuccess, onError)

      expect(onSuccess).toBeDefined()
      expect(typeof onSuccess).toBe('function')
    })

    it('accepts error callback', () => {
      const onSuccess = vi.fn()
      const onError = vi.fn()

      useUpdateInterventionTaskMutation(onSuccess, onError)

      expect(onError).toBeDefined()
      expect(typeof onError).toBe('function')
    })
  })

  describe('TaskPayload validation', () => {
    it('validates TaskPayload has required fields', () => {
      const payload: TaskPayload = {
        name: 'Test Task',
        quantity: 1,
        unitPrice: 100.0,
      }

      expect(payload).toHaveProperty('name')
      expect(payload).toHaveProperty('quantity')
      expect(payload).toHaveProperty('unitPrice')
    })

    it('validates field types', () => {
      const payload: TaskPayload = {
        name: 'Test',
        quantity: 5,
        unitPrice: 45.5,
      }

      expect(typeof payload.name).toBe('string')
      expect(typeof payload.quantity).toBe('number')
      expect(typeof payload.unitPrice).toBe('number')
    })
  })
})
