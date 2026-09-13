import type { TaskOverviewDto } from '@/entities/task/types'
import type { ITaskList } from '@/entities/task-list/types'

export type DashboardDto = {
    recent_tasks: TaskOverviewDto[]
    recent_task_lists: ITaskList[]
}
