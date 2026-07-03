export const TASK_OWNER_ROLES = ['Project Manager', 'Executor', 'QA', 'Supervisor'] as const
export type TaskOwnerRole = (typeof TASK_OWNER_ROLES)[number]

export interface ITaskOwner {
    id: string
    user: { id: string; name: string }
    role: TaskOwnerRole | null
    is_primary: boolean
}

export interface TaskOwnerDraft {
    user_id: string
    user_name: string
    role: TaskOwnerRole | null
    is_primary: boolean
}

export interface SyncTaskOwnersPayload {
    owners: Array<{ user_id: string; role: TaskOwnerRole | null; is_primary: boolean }>
}
