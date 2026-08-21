import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteProjectDocumentRequest } from '../api'
import { ProjectDocumentQueryKey } from '../config'
import { useConfirmDialog } from '@/shared/composables/use.confirm-dialog'

export function useDeleteProjectDocumentMutation() {
    const queryClient = useQueryClient()
    const confirm = useConfirmDialog()

    const { mutate, ...rest } = useMutation({
        mutationFn: (id: string) => deleteProjectDocumentRequest(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
        },
    })

    async function mutateWithConfirm(id: string, title: string, onSuccess?: () => void) {
        const confirmed = await confirm.requireAsync({
            header: 'Delete Document',
            message: `Are you sure you want to delete "${title}"? This will also delete all nested documents, comments, attachments, tags, and task links.`,
            acceptLabel: 'Delete',
            rejectLabel: 'Cancel',
        })

        if (confirmed) {
            mutate(id, onSuccess ? { onSuccess } : undefined)
        }
    }

    return { mutate, mutateWithConfirm, ...rest }
}
