import { httpClient } from '@/shared/api'
import type { CreateTagPayload, ITag } from '../types'

type TagResponse = { data: ITag }
type TagsResponse = { data: ITag[] }

export async function createTagRequest(data: CreateTagPayload): Promise<TagResponse> {
    return httpClient.post<TagResponse>('/tags', data).then((res) => res.data)
}

export async function searchTagsRequest(search?: string): Promise<TagsResponse> {
    return httpClient.get<TagsResponse>('/tags', { params: { search } }).then((res) => res.data)
}
