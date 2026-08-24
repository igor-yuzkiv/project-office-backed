import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCreateProjectDocumentMutation } from '@/entities/project-document'
import type { IProjectDocument, ProjectDocumentPathNodeDto } from '@/entities/project-document/types'
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

export interface ProjectDocumentCreateDialogOptions {
    // Where the caller wants to go once the document exists. Callers that keep the
    // user on their own screen pass this instead of the default jump to the document.
    onCreated?: (document: IProjectDocument) => void
}

export function useProjectDocumentCreateDialog(options?: ProjectDocumentCreateDialogOptions) {
    const router = useRouter()
    const toast = useToast()

    const visible = ref(false)
    const projectId = ref<string | null>(null)
    const parentDocument = ref<ProjectDocumentPathNodeDto | null>(null)
    const formData = ref<ProjectDocumentCreateFormData>(getDefaultFormData())
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending } = useCreateProjectDocumentMutation()

    function open(id: string, parent?: ProjectDocumentPathNodeDto) {
        projectId.value = id
        parentDocument.value = parent ?? null
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
            {
                project_id: projectId.value,
                title: formData.value.title,
                parent_id: parentDocument.value?.id,
            },
            {
                onSuccess: (response) => {
                    close()

                    if (options?.onCreated) {
                        options.onCreated(response.data)
                        return
                    }

                    router.push({
                        name: 'project-documentation.document',
                        params: { projectId: response.data.project_id, documentId: response.data.id },
                    })
                },
                onError: handleError,
            }
        )
    }

    return {
        visible,
        formData,
        parentDocument,
        validationErrors,
        isPending,
        open,
        close,
        submit,
    }
}
