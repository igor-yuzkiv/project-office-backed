import type { AttachmentRole } from '@/entities/attachment/types'

export const ProjectDocumentAttachmentRoles = {
    CONTENT: 'project_document.content',
    COMMENTS: 'project_document.comments',
} as const satisfies Record<string, AttachmentRole>
