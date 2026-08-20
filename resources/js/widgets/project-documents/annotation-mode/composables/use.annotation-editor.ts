import { type MaybeRefOrGetter } from 'vue'
import { useDeleteAnnotationMutation, useUpdateAnnotationMutation, type SaveAnnotationDto } from '@/entities/annotation'
import { useCreateProjectDocumentAnnotationMutation } from '@/entities/project-document'
import { ApiError } from '@/shared/api/api.error'
import { useToast } from '@/shared/composables/use.toast'

export function useAnnotationEditor(documentId: MaybeRefOrGetter<string>) {
    const toast = useToast()
    const createMutation = useCreateProjectDocumentAnnotationMutation(documentId)
    const updateMutation = useUpdateAnnotationMutation()
    const deleteMutation = useDeleteAnnotationMutation()

    function report(error: unknown, fallback: string) {
        toast.error(error instanceof ApiError ? error.displayMessage : fallback)
    }

    /** Resolves to false when saving failed, so the caller can keep the typed text on screen. */
    async function create(data: SaveAnnotationDto): Promise<boolean> {
        try {
            await createMutation.mutateAsync(data)

            return true
        } catch (error) {
            report(error, 'Failed to save annotation.')

            return false
        }
    }

    async function update(annotationId: string, data: SaveAnnotationDto): Promise<boolean> {
        try {
            await updateMutation.mutateAsync({ annotationId, data })

            return true
        } catch (error) {
            report(error, 'Failed to save annotation.')

            return false
        }
    }

    return {
        create,
        update,
        remove: deleteMutation.mutateWithConfirm,
        isSaving: createMutation.isPending,
        isUpdating: updateMutation.isPending,
    }
}
