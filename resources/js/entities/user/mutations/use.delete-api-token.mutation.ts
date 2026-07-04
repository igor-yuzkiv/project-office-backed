import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteApiTokenRequest } from '../api'
import { ApiTokenQueryKey } from '../config'
import { useConfirmDialog } from '@/shared/composables/use.confirm-dialog'

export function useDeleteApiTokenMutation() {
    const queryClient = useQueryClient()
    const confirm = useConfirmDialog()

    const { mutate, ...rest } = useMutation({
        mutationFn: (id: string) => deleteApiTokenRequest(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ApiTokenQueryKey.all })
        },
    })

    async function mutateWithConfirm(id: string, message?: string) {
        const confirmed = await confirm.requireAsync({
            header: 'Revoke Token',
            message: message ?? 'Are you sure you want to revoke this API token? This cannot be undone.',
            acceptLabel: 'Revoke',
            rejectLabel: 'Cancel',
        })

        if (confirmed) {
            mutate(id)
        }
    }

    return { mutate, mutateWithConfirm, ...rest }
}
