import { describe, expect, it } from 'vitest'
import { openTasksCount } from './open-tasks-count'

describe('openTasksCount', () => {
    it('sums every status except completed, closed and declined', () => {
        expect(
            openTasksCount({
                backlog: 1,
                open: 2,
                ready_for_development: 3,
                in_progress: 4,
                ready_to_test: 5,
                completed: 10,
                closed: 20,
                declined: 30,
            })
        ).toBe(15)
    })

    it('returns 0 when only closed statuses have tasks', () => {
        expect(openTasksCount({ completed: 2, closed: 1, declined: 3 })).toBe(0)
    })

    it('returns 0 without counts', () => {
        expect(openTasksCount(undefined)).toBe(0)
    })
})
