import type { IProjectDocument, ProjectDocumentOverviewDto } from './project-document.types'

export type ProjectDocumentInclude = 'project' | 'tags' | 'tasks' | 'createdBy' | 'updatedBy'

export interface IProjectDocumentResponse {
    data: IProjectDocument
}

export interface IProjectDocumentsResponse {
    data: ProjectDocumentOverviewDto[]
}

export interface ProjectDocumentFetchParams {
    include?: ProjectDocumentInclude[]
}

export interface ICreateProjectDocumentInput {
    project_id: string
    title: string
    tag_ids?: string[]
}

export interface IUpdateProjectDocumentInput {
    title?: string
    content?: string
    tag_ids?: string[]
}
