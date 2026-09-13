import { describe, expect, it } from 'vitest'
import type { TaskOverviewDto } from '@/entities/task/types'
import { taskNeighbours } from './task-neighbours'

const tasks = [{ id: 'a' }, { id: 'b' }, { id: 'c' }] as TaskOverviewDto[]

describe('taskNeighbours', () => {
    it('returns both neighbours of a middle task', () => {
        expect(taskNeighbours(tasks, 'b')).toEqual({ previous: tasks[0], next: tasks[2] })
    })

    it('has no previous for the first and no next for the last task', () => {
        expect(taskNeighbours(tasks, 'a')).toEqual({ previous: null, next: tasks[1] })
        expect(taskNeighbours(tasks, 'c')).toEqual({ previous: tasks[1], next: null })
    })

    it('has no neighbours for a task that is not in the list', () => {
        expect(taskNeighbours(tasks, 'zzz')).toEqual({ previous: null, next: null })
        expect(taskNeighbours([], 'a')).toEqual({ previous: null, next: null })
    })
})
