import type { PagingParams } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { SortParams } from '@/shared/sort'
import type { IProjectDocument, ProjectDocumentOverviewDto, ProjectDocumentStatusValue } from './project-document.types'

export type ProjectDocumentInclude = 'project' | 'tags' | 'tasks' | 'createdBy' | 'updatedBy'

export type ProjectDocumentSearchParams = PagingParams &
    SortParams & {
        query?: string
        filters?: FilterPayloadItem[]
        include?: ProjectDocumentInclude[]
    }

export interface IProjectDocumentResponse {
    data: IProjectDocument
}

export interface IProjectDocumentsResponse {
    data: ProjectDocumentOverviewDto[]
}

export interface ProjectDocumentFetchParams {
    include?: ProjectDocumentInclude[]
    with_path?: boolean
}

export type ProjectDocumentTreeFetchParams = PagingParams & {
    parent_id?: string | null
    filters?: FilterPayloadItem[]
}

export interface ICreateProjectDocumentInput {
    project_id: string
    title: string
    parent_id?: string
    tag_ids?: string[]
}

export interface IUpdateProjectDocumentInput {
    title?: string
    content?: string
    status?: ProjectDocumentStatusValue
    tag_ids?: string[]
}

export interface IMoveProjectDocumentInput {
    parent_id: string | null
}
