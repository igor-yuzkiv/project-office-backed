import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { AttachmentRole } from '@/entities/attachment/types'
import { uploadTaskListAttachmentRequest } from '../api'
import { TaskListAttachmentQueryKey } from '../config'

interface UploadTaskListAttachmentInput {
    file: File
    role?: AttachmentRole
}

export function useUploadTaskListAttachmentMutation(taskListId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ file, role }: UploadTaskListAttachmentInput) =>
            uploadTaskListAttachmentRequest(toValue(taskListId), file, role),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: TaskListAttachmentQueryKey.taskListAttachments(taskListId),
            })
        },
    })
}
