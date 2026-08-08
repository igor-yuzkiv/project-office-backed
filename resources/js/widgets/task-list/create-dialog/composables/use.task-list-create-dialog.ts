import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCreateTaskListMutation } from '@/entities/task-list/mutations'
import type { ITaskList } from '@/entities/task-list/types'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'

export interface TaskListCreateFormData {
    name: string
    project: ProjectOverviewDto | null
}

function getDefaultFormData(): TaskListCreateFormData {
    return { name: '', project: null }
}

interface TaskListCreateDialogOptions {
    /**
     * Lets a caller pick up the freshly created list, e.g. to select it in a lookup field.
     * Passing it also suppresses the navigation to the new list: a caller that handles the
     * result is in the middle of its own form and must not be sent away from it.
     */
    onCreated?: (taskList: ITaskList) => void
}

export function useTaskListCreateDialog(options: TaskListCreateDialogOptions = {}) {
    const router = useRouter()

    const visible = ref(false)
    const formData = ref<TaskListCreateFormData>(getDefaultFormData())
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending } = useCreateTaskListMutation()

    /** Omit the project to let the user pick one; pass it to pin the list to that project. */
    function open(project?: ProjectOverviewDto) {
        formData.value = { name: '', project: project ?? null }
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

        create(
            { project_id: formData.value.project.id, name: formData.value.name },
            {
                onSuccess: (response) => {
                    close()

                    if (options.onCreated) {
                        options.onCreated(response.data)

                        return
                    }

                    router.push({ name: 'task-list-details.tasks', params: { id: response.data.id } })
                },
                onError: handleError,
            }
        )
    }

    return { visible, formData, validationErrors, isPending, open, close, submit }
}
