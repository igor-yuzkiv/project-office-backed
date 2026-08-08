import type { AttachmentRole } from '@/entities/attachment/types'

export const TaskListAttachmentRoles = {
    UPLOAD: 'task-lists.upload',
    DESCRIPTION: 'task-lists.description',
    COMMENTS: 'task-lists.comments',
} as const satisfies Record<string, AttachmentRole>
