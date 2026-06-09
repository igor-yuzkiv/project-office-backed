import type { AttachmentRole } from '@/shared/types'

export const TASK_ATTACHMENT_ROLES = {
    DESCRIPTION: 'task_description',
} as const satisfies Record<string, AttachmentRole>
