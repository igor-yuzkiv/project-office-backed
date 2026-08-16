import type { TaskOverviewDto } from '@/entities/task/types'
import type { ITaskList } from '@/entities/task-list/types'

/**
 * `key` mirrors a task view from the backend registry and stays a plain string: a view added there
 * must render with a fallback icon, not turn into a compile error here.
 */
export type DashboardTaskViewDto = {
    key: string
    label: string
    count: number
}

export type DashboardSummaryDto = {
    task_views: DashboardTaskViewDto[]
    projects_count: number
    task_lists_count: number
}

/** The endpoint always counts the tasks of a listed task list. */
export type DashboardTaskListDto = ITaskList & {
    tasks_count: number
}

export type DashboardDto = {
    summary: DashboardSummaryDto
    recent_tasks: TaskOverviewDto[]
    recent_task_lists: DashboardTaskListDto[]
}
