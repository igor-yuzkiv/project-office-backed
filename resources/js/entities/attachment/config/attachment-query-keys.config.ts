import type { MaybeRefOrGetter } from 'vue'
import type { AttachmentSearchParams } from '../types/attachment.types'

export const AttachmentQueryKey = {
    all: ['attachments'] as const,
    search: (params: MaybeRefOrGetter<AttachmentSearchParams>) =>
        [...AttachmentQueryKey.all, 'search', params] as const,
}
