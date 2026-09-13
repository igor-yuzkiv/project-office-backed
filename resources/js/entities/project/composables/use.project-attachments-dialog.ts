import type { MaybeRefOrGetter } from 'vue'
import { useAttachmentsDialog } from '@/entities/attachment/composables'
import { ProjectAttachmentRoles } from '../config/project-attachment.config'
import { useProjectAttachmentsQuery } from '../queries/use.project-attachments.query'
import { useUploadProjectAttachmentMutation } from '../mutations/use.upload-project-attachment.mutation'

export function useProjectAttachmentsDialog(projectId: MaybeRefOrGetter<string>) {
    return useAttachmentsDialog(
        useProjectAttachmentsQuery(projectId),
        useUploadProjectAttachmentMutation(projectId),
        ProjectAttachmentRoles.UPLOAD
    )
}
