import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCreateProjectDocumentMutation, useUpdateProjectDocumentMutation } from '@/entities/project-document'
import type {
    IProjectDocument,
    ProjectDocumentPathNodeDto,
    ProjectDocumentStatusValue,
} from '@/entities/project-document/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'

export interface ProjectDocumentUpsertFormData {
    title: string
    status: ProjectDocumentStatusValue
    tags: ITag[]
}

// The same status the backend gives a document created without one.
const DEFAULT_STATUS: ProjectDocumentStatusValue = 'draft'

export interface ProjectDocumentUpsertDialogOptions {
    // Where the caller wants to go once the document exists. Callers that keep the
    // user on their own screen pass this instead of the default jump to the document.
    onCreated?: (document: IProjectDocument) => void
    onUpdated?: (document: IProjectDocument) => void
}

/**
 * One dialog for a document's own fields — title, status, tags — whether the document is being
 * made or changed. Content is not here: it belongs to a version and is written on the sheet.
 */
export function useProjectDocumentUpsertDialog(options?: ProjectDocumentUpsertDialogOptions) {
    const router = useRouter()
    const toast = useToast()

    const visible = ref(false)
    const mode = ref<'create' | 'edit'>('create')
    const projectId = ref<string | null>(null)
    const documentId = ref<string | null>(null)
    const parentDocument = ref<ProjectDocumentPathNodeDto | null>(null)
    const formData = ref<ProjectDocumentUpsertFormData>({ title: '', status: DEFAULT_STATUS, tags: [] })
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending: isCreating } = useCreateProjectDocumentMutation()
    const { mutate: update, isPending: isUpdating } = useUpdateProjectDocumentMutation()

    function show(next: ProjectDocumentUpsertFormData) {
        formData.value = next
        validationErrors.value = {}
        visible.value = true
    }

    function openCreate(id: string, parent?: ProjectDocumentPathNodeDto) {
        mode.value = 'create'
        projectId.value = id
        documentId.value = null
        parentDocument.value = parent ?? null
        show({ title: '', status: DEFAULT_STATUS, tags: [] })
    }

    function openEdit(document: IProjectDocument) {
        mode.value = 'edit'
        projectId.value = document.project_id
        documentId.value = document.id
        // The last node of the path is the document itself.
        parentDocument.value = document.path?.at(-2) ?? null
        show({ title: document.title, status: document.status, tags: [...(document.tags ?? [])] })
    }

    function close() {
        visible.value = false
    }

    function handleError(error: unknown) {
        if (error instanceof ApiError && error.isValidationError) {
            validationErrors.value = error.validationErrors ?? {}
        } else {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save document.')
        }
    }

    function submit() {
        validationErrors.value = {}

        const title = formData.value.title.trim()
        const tagIds = formData.value.tags.map((tag) => tag.id)

        if (mode.value === 'edit') {
            if (!documentId.value) return

            update(
                { id: documentId.value, data: { title, status: formData.value.status, tag_ids: tagIds } },
                {
                    onSuccess: (response) => {
                        close()
                        options?.onUpdated?.(response.data)
                    },
                    onError: handleError,
                }
            )

            return
        }

        if (!projectId.value) return

        create(
            {
                project_id: projectId.value,
                title,
                parent_id: parentDocument.value?.id,
                status: formData.value.status,
                tag_ids: tagIds,
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
        mode,
        formData,
        parentDocument,
        validationErrors,
        isPending: isCreating || isUpdating,
        openCreate,
        openEdit,
        submit,
    }
}
