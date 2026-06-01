import type { IEntity } from '@/shared/types'

export interface IUser extends IEntity {
    name: string
    email: string
}

export type UserOverviewDto = Pick<IUser, 'id' | 'name'>

export interface ILoginCredentials {
    email: string
    password: string
    remember?: boolean
}
