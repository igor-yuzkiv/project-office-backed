import type { AttachmentRole } from '@/entities/attachment/types'

export const TaskAttachmentRoles = {
    UPLOAD: 'tasks.upload',
    DESCRIPTION: 'tasks.description',
    COMMENTS: 'task.comments',
} as const satisfies Record<string, AttachmentRole>
