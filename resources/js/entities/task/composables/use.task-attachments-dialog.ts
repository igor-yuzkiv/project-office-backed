import { computed, type MaybeRefOrGetter } from 'vue'
import { useToast } from '@/shared/composables/use.toast'
import { ApiError } from '@/shared/api/api.error'
import type { IAttachment } from '@/entities/attachment/types'
import { useDeleteAttachmentMutation } from '@/entities/attachment/mutations'
import { useDownloadAttachment } from '@/entities/attachment/composables'
import { TaskAttachmentRoles } from '../config/task-attachment.config'
import { useTaskAttachmentsQuery } from '../queries/use.task-attachments.query'
import { useUploadTaskAttachmentMutation } from '../mutations/use.upload-task-attachment.mutation'

export function useTaskAttachmentsDialog(taskId: MaybeRefOrGetter<string>) {
    const toast = useToast()
    const { attachments, paginationMeta, isPending } = useTaskAttachmentsQuery(taskId)
    const uploadMutation = useUploadTaskAttachmentMutation(taskId)
    const { mutateWithConfirm: deleteAttachment } = useDeleteAttachmentMutation()
    const { download } = useDownloadAttachment()

    const count = computed(() => paginationMeta.value?.total ?? attachments.value.length)

    async function upload(file: File) {
        try {
            await uploadMutation.mutateAsync({ file, role: TaskAttachmentRoles.UPLOAD })
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
