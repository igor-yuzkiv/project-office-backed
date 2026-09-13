import type { MaybeRefOrGetter } from 'vue'
import { useAttachmentsDialog } from '@/entities/attachment/composables'
import { TaskListAttachmentRoles } from '../config/task-list-attachment.config'
import { useTaskListAttachmentsQuery } from '../queries/use.task-list-attachments.query'
import { useUploadTaskListAttachmentMutation } from '../mutations/use.upload-task-list-attachment.mutation'

export function useTaskListAttachmentsDialog(taskListId: MaybeRefOrGetter<string>) {
    return useAttachmentsDialog(
        useTaskListAttachmentsQuery(taskListId),
        useUploadTaskListAttachmentMutation(taskListId),
        TaskListAttachmentRoles.UPLOAD
    )
}
