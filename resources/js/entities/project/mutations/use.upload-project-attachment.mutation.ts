import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { AttachmentRole } from '@/entities/attachment/types'
import { uploadProjectAttachmentRequest } from '../api'
import { ProjectAttachmentQueryKey } from '../config'

interface UploadProjectAttachmentInput {
    file: File
    role?: AttachmentRole
}

export function useUploadProjectAttachmentMutation(projectId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: ({ file, role }: UploadProjectAttachmentInput) =>
            uploadProjectAttachmentRequest(toValue(projectId), file, role),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: ProjectAttachmentQueryKey.projectAttachments(projectId),
            })
        },
    })
}
