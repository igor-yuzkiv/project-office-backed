import { httpClient } from '@/shared/api'
import type { TaskViewDto } from '../types'

export async function fetchTaskViewsRequest(): Promise<TaskViewDto[]> {
    return httpClient.get<{ data: TaskViewDto[] }>('/task-views').then((res) => res.data.data)
}
