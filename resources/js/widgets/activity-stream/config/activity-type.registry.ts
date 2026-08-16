import type { AuditRecordSubjectType, KnownAuditRecordSubjectType } from '@/entities/audit-record'

/**
 * Accents group by what happened, not by which domain it happened in: in a mixed feed a creation
 * and a deletion from different domains sit next to each other, and the action is what the eye
 * picks up first.
 */
type ActivityAccent = 'create' | 'update' | 'status' | 'delete' | 'talk' | 'agent' | 'none'

export type ActivityTypeDef = {
    icon: string
    accent: ActivityAccent
    /** Whether this kind of event points anywhere. A deletion never does. */
    linkable: boolean
}

export const ACTIVITY_TYPE_REGISTRY: Record<string, ActivityTypeDef> = {
    'task.created': { icon: 'heroicons:plus', accent: 'create', linkable: true },
    'task.updated': { icon: 'heroicons:pencil-square', accent: 'update', linkable: true },
    'task.status_changed': { icon: 'heroicons:arrow-right', accent: 'status', linkable: true },
    'task.deleted': { icon: 'heroicons:trash', accent: 'delete', linkable: false },
    'task.bulk_status_changed': { icon: 'heroicons:arrows-right-left', accent: 'status', linkable: false },

    'task.started': { icon: 'heroicons:play', accent: 'agent', linkable: true },
    'task.checkpoint': { icon: 'heroicons:chat-bubble-bottom-center-text', accent: 'talk', linkable: true },
    'task.handoff': { icon: 'heroicons:arrow-right-circle', accent: 'agent', linkable: true },

    'comment.created': { icon: 'heroicons:chat-bubble-left-right', accent: 'talk', linkable: true },

    'task_list.created': { icon: 'heroicons:queue-list', accent: 'create', linkable: true },
    'task_list.updated': { icon: 'heroicons:pencil-square', accent: 'update', linkable: true },
    'task_list.tasks_added': { icon: 'heroicons:plus-circle', accent: 'create', linkable: true },

    'project.created': { icon: 'heroicons:folder-plus', accent: 'create', linkable: true },
    'project.updated': { icon: 'heroicons:pencil-square', accent: 'update', linkable: true },
    'project.deleted': { icon: 'heroicons:trash', accent: 'delete', linkable: false },

    'project_document.created': { icon: 'heroicons:document-plus', accent: 'create', linkable: true },
    'project_document.updated': { icon: 'heroicons:document-text', accent: 'update', linkable: true },

    'attachment.uploaded': { icon: 'heroicons:paper-clip', accent: 'create', linkable: true },
}

export const UNKNOWN_ACTIVITY_TYPE: ActivityTypeDef = {
    icon: 'heroicons:question-mark-circle',
    accent: 'none',
    linkable: false,
}

export function resolveActivityType(type: string): ActivityTypeDef {
    return ACTIVITY_TYPE_REGISTRY[type] ?? UNKNOWN_ACTIVITY_TYPE
}

export const ACTIVITY_ACCENT_CLASSES: Record<ActivityAccent, string> = {
    create: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400',
    update: 'bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
    status: 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400',
    delete: 'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400',
    talk: 'bg-violet-100 text-violet-600 dark:bg-violet-950 dark:text-violet-400',
    agent: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-950 dark:text-cyan-400',
    none: 'bg-surface-200 text-surface-500 dark:bg-surface-700 dark:text-surface-300',
}

const SUBJECT_ROUTE_NAMES: Record<KnownAuditRecordSubjectType, string> = {
    task: 'task-details',
    task_list: 'task-list-details',
    project: 'project-details',
    project_document: 'project-document-details',
}

/**
 * The backend derives subject.type from a class name, so an unseen value is possible; such a row
 * simply gets no link.
 */
export function resolveSubjectRouteName(type: AuditRecordSubjectType): string | null {
    return SUBJECT_ROUTE_NAMES[type as KnownAuditRecordSubjectType] ?? null
}
