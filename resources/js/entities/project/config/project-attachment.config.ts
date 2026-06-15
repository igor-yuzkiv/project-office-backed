import type { AttachmentRole } from '@/entities/attachment/types'

export const PROJECT_ATTACHMENT_ROLES = {
    DESCRIPTION: 'projects.description',
} as const satisfies Record<string, AttachmentRole>
