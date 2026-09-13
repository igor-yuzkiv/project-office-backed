import type { TaskOverviewDto } from '@/entities/task/types'

export type TaskNeighbours = { previous: TaskOverviewDto | null; next: TaskOverviewDto | null }

/**
 * Neighbours in the order the tasks arrived. A task that is not among them has none — guessing
 * at rows nobody fetched would send the reader somewhere the rail does not show.
 */
export function taskNeighbours(tasks: TaskOverviewDto[], currentTaskId: string): TaskNeighbours {
    const index = tasks.findIndex((task) => task.id === currentTaskId)
    if (index < 0) return { previous: null, next: null }

    return {
        previous: tasks[index - 1] ?? null,
        next: tasks[index + 1] ?? null,
    }
}
