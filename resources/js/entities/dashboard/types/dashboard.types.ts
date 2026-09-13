import type { TaskOverviewDto } from '@/entities/task/types'
import type { ITaskList } from '@/entities/task-list/types'

export type DashboardDto = {
    recent_tasks: TaskOverviewDto[]
    recent_task_lists: ITaskList[]
}

// The endpoint no longer returns a summary. These shapes only keep the unused
// widgets/home-dashboard compiling until they are deleted; remove them together.
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

export type DashboardTaskListDto = ITaskList & {
    tasks_count: number
}
