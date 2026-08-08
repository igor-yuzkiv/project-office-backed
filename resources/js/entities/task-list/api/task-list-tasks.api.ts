import { httpClient } from '@/shared/api'
import type { TaskOverviewDto } from '@/entities/task/types'
import type { IAddTasksToTaskListInput } from '../types'

type AddedTasksResponse = { data: TaskOverviewDto[] }

/** Adds only — tasks already belonging to a list are rejected by the backend, never moved. */
export async function addTasksToTaskListRequest(
    taskListId: string,
    data: IAddTasksToTaskListInput
): Promise<AddedTasksResponse> {
    return httpClient.post<AddedTasksResponse>(`/task-lists/${taskListId}/tasks`, data).then((res) => res.data)
}
