import type { IEntity } from '@/shared/types'

export interface IUser extends IEntity {
    name: string
    email: string
    initials: string
    avatar_url: string | null
}

export type UserOverviewDto = Pick<IUser, 'id' | 'name' | 'initials' | 'avatar_url'>

export interface ILoginCredentials {
    email: string
    password: string
    remember?: boolean
}

export interface IApiToken extends IEntity {
    name: string
    expires_at: string | null
    created_at: string
}

export interface CreateApiTokenPayload {
    name: string
    expires_at: string
}

export interface CreateApiTokenResult {
    token: IApiToken
    plain_text_token: string
}
