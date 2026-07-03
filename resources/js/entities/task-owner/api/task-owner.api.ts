import { httpClient } from '@/shared/api'
import type { ITaskOwner, SyncTaskOwnersPayload } from '../types'

type TaskOwnersResponse = { data: ITaskOwner[] }

export async function fetchTaskOwnersRequest(taskId: string): Promise<TaskOwnersResponse> {
    return httpClient.get<TaskOwnersResponse>(`/tasks/${taskId}/owners`).then((res) => res.data)
}

export async function syncTaskOwnersRequest(
    taskId: string,
    payload: SyncTaskOwnersPayload
): Promise<TaskOwnersResponse> {
    return httpClient.put<TaskOwnersResponse>(`/tasks/${taskId}/owners`, payload).then((res) => res.data)
}
