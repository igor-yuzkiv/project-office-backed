import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { uploadAttachmentRequest } from '../api'
import { AttachmentQueryKey } from '../config'

export function useUploadAttachmentMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: uploadAttachmentRequest,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: AttachmentQueryKey.all })
        },
    })
}
