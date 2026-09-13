import type { AuditRecordSubjectType, KnownAuditRecordSubjectType } from '../types'

export type ActivityTypeDef = {
    /** Whether this kind of event points anywhere. A deletion never does. */
    linkable: boolean
}

export const ACTIVITY_TYPE_REGISTRY: Record<string, ActivityTypeDef> = {
    'task.created': { linkable: true },
    'task.updated': { linkable: true },
    'task.status_changed': { linkable: true },
    'task.deleted': { linkable: false },
    'task.bulk_status_changed': { linkable: false },

    'task.started': { linkable: true },
    'task.checkpoint': { linkable: true },
    'task.handoff': { linkable: true },

    'comment.created': { linkable: true },

    'task_list.created': { linkable: true },
    'task_list.updated': { linkable: true },
    'task_list.tasks_added': { linkable: true },

    'project.created': { linkable: true },
    'project.updated': { linkable: true },
    'project.deleted': { linkable: false },

    'project_document.created': { linkable: true },
    'project_document.updated': { linkable: true },

    'project_document_version.created': { linkable: true },
    'project_document_version.updated': { linkable: true },
    // Unlike the other deletions this one still links: the version is gone, the document is not.
    'project_document_version.deleted': { linkable: true },
    'project_document_version.primary_changed': { linkable: true },

    'attachment.uploaded': { linkable: true },
}

/** Sentence-case labels for the Type filter, one per registry key; the single list a chip offers. */
export const ACTIVITY_TYPE_LABELS: Record<string, string> = {
    'task.created': 'Task created',
    'task.updated': 'Task updated',
    'task.status_changed': 'Task status changed',
    'task.deleted': 'Task deleted',
    'task.bulk_status_changed': 'Tasks status changed',
    'task.started': 'Task started',
    'task.checkpoint': 'Checkpoint',
    'task.handoff': 'Handoff',
    'comment.created': 'Comment',
    'task_list.created': 'Task list created',
    'task_list.updated': 'Task list updated',
    'task_list.tasks_added': 'Tasks added to list',
    'project.created': 'Project created',
    'project.updated': 'Project updated',
    'project.deleted': 'Project deleted',
    'project_document.created': 'Document created',
    'project_document.updated': 'Document updated',
    'project_document_version.created': 'Document version created',
    'project_document_version.updated': 'Document version updated',
    'project_document_version.deleted': 'Document version deleted',
    'project_document_version.primary_changed': 'Primary version changed',
    'attachment.uploaded': 'Attachment uploaded',
}

export function resolveActivityType(type: string): ActivityTypeDef {
    return ACTIVITY_TYPE_REGISTRY[type] ?? { linkable: false }
}

const SUBJECT_ROUTE_NAMES: Record<KnownAuditRecordSubjectType, string> = {
    task: 'task-details',
    task_list: 'task-list-details',
    project: 'project-details',
    project_document: 'project-document-resolver',
}

/**
 * The backend derives subject.type from a class name, so an unseen value is possible; such a row
 * simply gets no link.
 */
export function resolveSubjectRouteName(type: AuditRecordSubjectType): string | null {
    return SUBJECT_ROUTE_NAMES[type as KnownAuditRecordSubjectType] ?? null
}
