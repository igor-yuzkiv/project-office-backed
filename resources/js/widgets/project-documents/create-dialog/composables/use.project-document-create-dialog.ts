import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCreateProjectDocumentMutation } from '@/entities/project-document'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'

export interface ProjectDocumentCreateFormData {
    title: string
}

export function getDefaultFormData(): ProjectDocumentCreateFormData {
    return {
        title: '',
    }
}

export function useProjectDocumentCreateDialog() {
    const router = useRouter()
    const toast = useToast()

    const visible = ref(false)
    const projectId = ref<string | null>(null)
    const formData = ref<ProjectDocumentCreateFormData>(getDefaultFormData())
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending } = useCreateProjectDocumentMutation()

    function open(id: string) {
        projectId.value = id
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
        } else {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to create document.')
        }
    }

    function submit() {
        if (!projectId.value) return

        validationErrors.value = {}

        create(
            { project_id: projectId.value, title: formData.value.title },
            {
                onSuccess: (response) => {
                    close()
                    router.push({ name: 'project-document-details', params: { id: response.data.id } })
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
