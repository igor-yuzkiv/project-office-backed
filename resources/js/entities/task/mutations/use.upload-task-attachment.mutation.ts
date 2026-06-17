import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { AttachmentRole } from '@/entities/attachment/types'
import { uploadTaskAttachmentRequest } from '../api/task-attachments.api'
import { TaskAttachmentQueryKey } from '../config'

interface UploadTaskAttachmentInput {
    file: File
    role?: AttachmentRole
}

export function useUploadTaskAttachmentMutation(taskId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ file, role }: UploadTaskAttachmentInput) =>
            uploadTaskAttachmentRequest(toValue(taskId), file, role),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: TaskAttachmentQueryKey.taskAttachments(taskId),
            })
        },
    })
}
