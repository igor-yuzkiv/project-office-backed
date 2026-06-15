import { computed, ref } from 'vue'
import { useCreateTaskListMutation, useUpdateTaskListMutation } from '@/entities/task-list/mutations'
import type { ITaskList } from '@/entities/task-list/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'

export interface TaskListUpsertFormData {
    name: string
    project: ProjectOverviewDto | null
}

function getDefaultFormData(): TaskListUpsertFormData {
    return { name: '', project: null }
}

export function useTaskListUpsertDialog() {
    const visible = ref(false)
    const editingTaskList = ref<ITaskList | undefined>()
    const mode = computed<'create' | 'update'>(() => (editingTaskList.value ? 'update' : 'create'))

    const formData = ref<TaskListUpsertFormData>(getDefaultFormData())
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending: isCreating } = useCreateTaskListMutation()
    const { mutate: update, isPending: isUpdating } = useUpdateTaskListMutation()
    const isPending = computed(() => isCreating.value || isUpdating.value)

    function open(project: ProjectOverviewDto, taskList?: ITaskList) {
        editingTaskList.value = taskList
        formData.value = { name: taskList?.name ?? '', project }
        validationErrors.value = {}
        visible.value = true
    }

    function close() {
        visible.value = false
    }

    function handleError(error: unknown) {
        if (error instanceof ApiError && error.isValidationError) {
            validationErrors.value = error.validationErrors ?? {}
        }
    }

    function submit() {
        if (!formData.value.project) return

        validationErrors.value = {}

        if (mode.value === 'create') {
            create(
                { project_id: formData.value.project.id, name: formData.value.name },
                { onSuccess: close, onError: handleError }
            )
        } else if (editingTaskList.value) {
            update(
                { taskListId: editingTaskList.value.id, data: { name: formData.value.name } },
                { onSuccess: close, onError: handleError }
            )
        }
    }

    return { visible, mode, formData, validationErrors, isPending, open, close, submit }
}
