import type { IEntity } from '@/shared/types'
import type { UserOverviewDto } from '@/entities/user/types'

export interface IProjectDocumentVersion extends IEntity {
    version_number: number
    label: string | null
    content: string | null
    /**
     * The version a reader gets when they have not picked one: the pinned version, or the newest
     * when nothing is pinned. Resolved by the backend, because one row cannot answer it.
     */
    is_primary: boolean
    author?: UserOverviewDto
    created_at: string
    updated_at: string
}

export interface IProjectDocumentVersionsResponse {
    data: IProjectDocumentVersion[]
}
