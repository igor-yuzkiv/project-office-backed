import { describe, expect, it } from 'vitest'
import { projectTaskCounts } from './project-task-counts'

describe('projectTaskCounts', () => {
    it('derives every count from the status table', () => {
        expect(
            projectTaskCounts({
                backlog: 1,
                open: 2,
                ready_for_development: 3,
                in_progress: 4,
                ready_to_test: 5,
                completed: 10,
                closed: 20,
                declined: 30,
            })
        ).toEqual({ open: 9, inProgress: 4, toTest: 5, backlog: 1, closed: 50, tasks: 45 })
    })

    it('treats missing statuses as zero', () => {
        expect(projectTaskCounts({ in_progress: 2 })).toEqual({
            open: 2,
            inProgress: 2,
            toTest: 0,
            backlog: 0,
            closed: 0,
            tasks: 2,
        })
    })

    it('returns zeros without counts', () => {
        expect(projectTaskCounts(undefined)).toEqual({
            open: 0,
            inProgress: 0,
            toTest: 0,
            backlog: 0,
            closed: 0,
            tasks: 0,
        })
    })
})
