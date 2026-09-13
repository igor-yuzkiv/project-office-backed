import { computed, type Ref } from 'vue'
import { useToast } from '@/shared/composables/use.toast'
import { ApiError } from '@/shared/api/api.error'
import type { PaginationMeta } from '@/shared/types'
import type { AttachmentRole, IAttachment } from '../types'
import { useDeleteAttachmentMutation } from '../mutations'
import { useDownloadAttachment } from './use.download-attachment'

export interface AttachmentsSource {
    attachments: Ref<IAttachment[]>
    paginationMeta: Ref<PaginationMeta | undefined>
    isPending: Ref<boolean>
}

export interface AttachmentUploadMutation {
    mutateAsync: (input: { file: File; role?: AttachmentRole }) => Promise<unknown>
    isPending: Ref<boolean>
}

/**
 * What `AttachmentsDialog` needs from an owner's attachments: the list, upload under the owner's
 * role, download and delete. The owner supplies its own query and upload mutation.
 */
export function useAttachmentsDialog(
    source: AttachmentsSource,
    uploadMutation: AttachmentUploadMutation,
    role: AttachmentRole
) {
    const toast = useToast()
    const { attachments, paginationMeta, isPending } = source
    const { mutateWithConfirm: deleteAttachment } = useDeleteAttachmentMutation()
    const { download } = useDownloadAttachment()

    const count = computed(() => paginationMeta.value?.total ?? attachments.value.length)

    async function upload(file: File) {
        try {
            await uploadMutation.mutateAsync({ file, role })
            toast.success('File uploaded successfully.')
        } catch (error) {
            toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to upload file.')
        }
    }

    function remove(attachment: IAttachment) {
        return deleteAttachment(attachment.id, `Are you sure you want to delete "${attachment.original_name}"?`)
    }

    return {
        attachments,
        isPending,
        isUploading: uploadMutation.isPending,
        count,
        upload,
        download,
        remove,
    }
}
