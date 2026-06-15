import { ref } from 'vue'
import { useCreateProjectMutation } from '@/entities/project/mutations'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'

export interface ProjectCreateFormData {
    name: string
}

function getDefaultFormData(): ProjectCreateFormData {
    return { name: '' }
}

export function useProjectCreateDialog() {
    const visible = ref(false)
    const formData = ref<ProjectCreateFormData>(getDefaultFormData())
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending } = useCreateProjectMutation()

    function open() {
        formData.value = getDefaultFormData()
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
        validationErrors.value = {}
        create({ name: formData.value.name }, { onSuccess: close, onError: handleError })
    }

    return { visible, formData, validationErrors, isPending, open, close, submit }
}
