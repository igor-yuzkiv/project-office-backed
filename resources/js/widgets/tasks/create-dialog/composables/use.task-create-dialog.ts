import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCreateTaskMutation } from '@/entities/task/mutations'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'

export interface TaskCreateFormData {
    name: string
    project: ProjectOverviewDto | null
}

export function getDefaultFormData(): TaskCreateFormData {
    return {
        name: '',
        project: null,
    }
}

export function useTaskCreateDialog() {
    const router = useRouter()

    const visible = ref(false)
    const formData = ref<TaskCreateFormData>(getDefaultFormData())
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending } = useCreateTaskMutation()

    function open(initialProject?: ProjectOverviewDto) {
        formData.value = { ...getDefaultFormData(), project: initialProject ?? null }
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
                    router.push({ name: 'task-details', params: { id: response.data.id } })
                },
                onError: handleError,
            }
        )
    }

    return {
        visible,
        formData,
        validationErrors,
        isPending,
        open,
        close,
        submit,
    }
}
