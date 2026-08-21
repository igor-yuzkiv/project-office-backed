import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteAnnotationRequest } from '../api'
import { AnnotationQueryKey } from '../config'
import { useConfirmDialog } from '@/shared/composables/use.confirm-dialog'
import { useToast } from '@/shared/composables/use.toast'
import { ApiError } from '@/shared/api/api.error'

export function useDeleteAnnotationMutation() {
    const queryClient = useQueryClient()
    const confirm = useConfirmDialog()
    const toast = useToast()

    const { mutate, ...rest } = useMutation({
        mutationFn: (annotationId: string) => deleteAnnotationRequest(annotationId),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: AnnotationQueryKey.all })
        },
        onError: (error) => {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to delete annotation.')
        },
    })

    async function mutateWithConfirm(annotationId: string) {
        const confirmed = await confirm.requireAsync({
            header: 'Delete Annotation',
            message: 'Are you sure you want to delete this annotation?',
            acceptLabel: 'Delete',
            rejectLabel: 'Cancel',
        })

        if (confirmed) {
            mutate(annotationId)
        }
    }

    return { mutate, mutateWithConfirm, ...rest }
}
