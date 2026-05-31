import type { IEntity } from '@/shared/types'

export interface IProject extends IEntity {
    name: string
    prefix: string
}

export interface ICreateProjectInput {
    name: string
    prefix?: string
}

export interface IUpdateProjectInput {
    name?: string
    prefix?: string
}
