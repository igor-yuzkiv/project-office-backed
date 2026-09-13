import { describe, expect, it } from 'vitest'
import { taskListProgress } from './task-list-progress'

describe('taskListProgress', () => {
    it('is empty without counts or with zeros', () => {
        expect(taskListProgress(undefined)).toEqual({ done: 0, inProgress: 0, total: 0 })
        expect(taskListProgress({ open: 0, completed: 0 })).toEqual({ done: 0, inProgress: 0, total: 0 })
    })

    it('leaves declined tasks out of the total', () => {
        expect(taskListProgress({ declined: 3 })).toEqual({ done: 0, inProgress: 0, total: 0 })
    })

    it('counts ready to test, completed and closed as done', () => {
        expect(
            taskListProgress({
                backlog: 1,
                open: 2,
                ready_for_development: 3,
                in_progress: 4,
                ready_to_test: 5,
                completed: 6,
                closed: 7,
                declined: 8,
            })
        ).toEqual({ done: 18, inProgress: 4, total: 28 })
    })
})
