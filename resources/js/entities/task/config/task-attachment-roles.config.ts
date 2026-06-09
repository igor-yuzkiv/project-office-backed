import type { AttachmentRole } from '@/shared/types'

export const TASK_ATTACHMENT_ROLES = {
    UPLOAD: 'tasks.upload',
    DESCRIPTION: 'tasks.description',
} as const satisfies Record<string, AttachmentRole>
