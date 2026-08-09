import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import { TaskQueryKey } from '@/entities/task/config'
import { addTasksToTaskListRequest } from '../api'
import { TaskListQueryKey } from '../config'

export function useAddTasksToTaskListMutation(taskListId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (taskIds: string[]) => addTasksToTaskListRequest(toValue(taskListId), { task_ids: taskIds }),
        onSuccess: () => {
            // Task searches drive both the list's own tasks and the candidate pool.
            queryClient.invalidateQueries({ queryKey: TaskQueryKey.all })
            queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })
        },
    })
}
