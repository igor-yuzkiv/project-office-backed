import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteProjectRequest } from '../api'
import { ProjectQueryKey } from '../config'
import { useConfirmDialog } from '@/shared/composables/use.confirm-dialog'

export function useDeleteProjectMutation() {
    const queryClient = useQueryClient()
    const confirm = useConfirmDialog()

    const { mutate, ...rest } = useMutation({
        mutationFn: (id: string) => deleteProjectRequest(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectQueryKey.all })
        },
    })

    async function mutateWithConfirm(id: string, message?: string) {
        const confirmed = await confirm.requireAsync({
            header: 'Delete',
            message: message ?? 'Are you sure you want to delete this item?',
            acceptLabel: 'Delete',
            rejectLabel: 'Cancel',
        })

        if (confirmed) {
            mutate(id)
        }
    }

    return { mutate, mutateWithConfirm, ...rest }
}
