import { ref } from 'vue'
import { useMoveProjectDocumentMutation } from '@/entities/project-document'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import type { ProjectDocumentMoveSelection } from '../ui/ProjectDocumentMoveDialog.vue'

export function useProjectDocumentMove(documentId: () => string) {
    const toast = useToast()

    const visible = ref(false)
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: move, isPending } = useMoveProjectDocumentMutation()

    function open() {
        validationErrors.value = {}
        visible.value = true
    }

    function close() {
        visible.value = false
    }

    function handleSelect(selection: ProjectDocumentMoveSelection) {
        validationErrors.value = {}

        move(
            { id: documentId(), data: { parent_id: selection.parentId } },
            {
                onSuccess: () => {
                    toast.success('Document moved.')
                },
                onError: (error: unknown) => {
                    if (error instanceof ApiError && error.isValidationError) {
                        validationErrors.value = error.validationErrors ?? {}
                        visible.value = true
                    } else {
                        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to move document.')
                    }
                },
            }
        )
    }

    return { visible, validationErrors, isPending, open, close, handleSelect }
}
