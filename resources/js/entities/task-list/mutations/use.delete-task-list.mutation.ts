import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteTaskListRequest } from '../api'
import { TaskListQueryKey } from '../config'
import { useConfirmDialog } from '@/shared/composables/use.confirm-dialog'

export function useDeleteTaskListMutation() {
    const queryClient = useQueryClient()
    const confirm = useConfirmDialog()

    const { mutate, ...rest } = useMutation({
        mutationFn: (id: string) => deleteTaskListRequest(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })
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
