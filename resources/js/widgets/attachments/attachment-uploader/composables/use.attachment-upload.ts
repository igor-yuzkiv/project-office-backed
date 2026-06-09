import { type MaybeRefOrGetter, toValue } from 'vue'
import type { AttachmentRole, ModuleName } from '@/shared/types'
import { ApiError } from '@/shared/api'
import { useToast } from '@/shared/composables'
import { useUploadAttachmentMutation } from '@/entities/attachment/mutations'

const DEFAULT_MAX_FILE_SIZE = 25 * 1024 * 1024

interface UseAttachmentUploadOptions {
    entityType: MaybeRefOrGetter<ModuleName>
    entityId: MaybeRefOrGetter<string>
    role?: MaybeRefOrGetter<AttachmentRole | null>
    maxFileSizeBytes?: MaybeRefOrGetter<number | undefined>
}

export function useAttachmentUpload(options: UseAttachmentUploadOptions) {
    const toast = useToast()
    const { mutate, isPending } = useUploadAttachmentMutation()

    function uploadFile(file: File) {
        const maxSize = toValue(options.maxFileSizeBytes) ?? DEFAULT_MAX_FILE_SIZE

        if (file.size > maxSize) {
            toast.error('File exceeds the maximum allowed size.')
            return
        }

        const role = toValue(options.role) ?? undefined

        mutate(
            {
                file,
                entity_type: toValue(options.entityType),
                entity_id: toValue(options.entityId),
                role,
            },
            {
                onSuccess: () => toast.success('File uploaded successfully.'),
                onError: (error) => {
                    toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to upload file.')
                },
            }
        )
    }

    return { uploadFile, isPending }
}
