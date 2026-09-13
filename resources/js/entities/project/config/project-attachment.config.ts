import type { MaybeRefOrGetter } from 'vue'
import type { PagingParams } from '@/shared/types'
import type { AttachmentRole } from '@/entities/attachment/types'

export const ProjectAttachmentRoles = {
    UPLOAD: 'projects.upload',
    DESCRIPTION: 'projects.description',
} as const satisfies Record<string, AttachmentRole>

export const ProjectAttachmentQueryKey = {
    projectAttachments: (projectId: MaybeRefOrGetter<string>) => ['attachments', 'projects', projectId] as const,
    projectAttachmentsPaginated: (projectId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) =>
        [...ProjectAttachmentQueryKey.projectAttachments(projectId), pagination] as const,
}
