import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteAttachmentRequest } from '../api'
import { AttachmentQueryKey } from '../config'
import { useConfirmDialog } from '@/shared/composables/use.confirm-dialog'

export function useDeleteAttachmentMutation() {
    const queryClient = useQueryClient()
    const confirm = useConfirmDialog()

    const { mutate, ...rest } = useMutation({
        mutationFn: (id: string) => deleteAttachmentRequest(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: AttachmentQueryKey.all })
        },
    })

    async function mutateWithConfirm(id: string, message?: string) {
        const confirmed = await confirm.requireAsync({
            header: 'Delete',
            message: message ?? 'Are you sure you want to delete this attachment?',
            acceptLabel: 'Delete',
            rejectLabel: 'Cancel',
        })

        if (confirmed) {
            mutate(id)
        }
    }

    return { mutate, mutateWithConfirm, ...rest }
}
