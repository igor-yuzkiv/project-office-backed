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

export interface ICreateProjectDocumentVersionInput {
    label?: string | null
    copy_content_from_version_id?: string | null
}

export interface IUpdateProjectDocumentVersionsInput {
    versions: Array<{ id: string; content: string | null; label: string | null }>
}

export interface ISetProjectDocumentPrimaryVersionInput {
    /** Null returns the document to following its newest version. */
    version_id: string | null
}

export interface IProjectDocumentVersionResponse {
    data: IProjectDocumentVersion
}
