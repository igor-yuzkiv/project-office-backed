import type { AttachmentRole } from '@/entities/attachment/types'

export const TASK_ATTACHMENT_ROLES = {
    UPLOAD: 'tasks.upload',
    DESCRIPTION: 'tasks.description',
} as const satisfies Record<string, AttachmentRole>
