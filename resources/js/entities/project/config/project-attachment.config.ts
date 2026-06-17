import type { AttachmentRole } from '@/entities/attachment/types'

export const ProjectAttachmentRoles = {
    DESCRIPTION: 'projects.description',
} as const satisfies Record<string, AttachmentRole>
