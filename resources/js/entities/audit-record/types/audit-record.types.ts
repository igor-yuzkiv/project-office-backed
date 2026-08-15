import type { UserOverviewDto } from '@/entities/user'

/**
 * Subjects the feed knows how to link to. The backend derives this value from a model class name
 * with no map behind it, so a new entity arrives here as a plain string the frontend has never
 * seen — hence the open end, and hence a row whose type is not one of these renders without a link
 * rather than failing to compile.
 */
export type KnownAuditRecordSubjectType = 'task' | 'task_list' | 'project' | 'project_document'

export type AuditRecordSubjectType = KnownAuditRecordSubjectType | (string & {})

export type AuditRecordSubjectDto = {
    type: AuditRecordSubjectType
    id: string
}

export type AuditRecordDto = {
    id: string
    /**
     * The machine type, e.g. 'task.status_changed'. Deliberately a plain string, not a union of
     * literals: an unknown type has to render as a generic row, and a union would turn a new
     * backend event into a compile error instead.
     */
    type: string
    title: string
    description: string | null
    created_at: string
    subject: AuditRecordSubjectDto | null
    actor: UserOverviewDto | null
}
