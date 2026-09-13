import type { MaybeRefOrGetter } from 'vue'
import { useAttachmentsDialog } from '@/entities/attachment/composables'
import { TaskAttachmentRoles } from '../config/task-attachment.config'
import { useTaskAttachmentsQuery } from '../queries/use.task-attachments.query'
import { useUploadTaskAttachmentMutation } from '../mutations/use.upload-task-attachment.mutation'

export function useTaskAttachmentsDialog(taskId: MaybeRefOrGetter<string>) {
    return useAttachmentsDialog(
        useTaskAttachmentsQuery(taskId),
        useUploadTaskAttachmentMutation(taskId),
        TaskAttachmentRoles.UPLOAD
    )
}
