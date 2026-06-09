import { computed, type MaybeRef, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { AttachmentSearchParams } from '../types'
import { searchAttachmentsRequest } from '../api'
import { AttachmentQueryKey } from '../config'

export function useAttachmentsSearchQuery(params: MaybeRef<AttachmentSearchParams>) {
    const { data, isPending, isError } = useQuery({
        queryKey: AttachmentQueryKey.search(params),
        queryFn: () => searchAttachmentsRequest(toValue(params)),
        placeholderData: keepPreviousData,
    })

    const attachments = computed(() => data.value?.data ?? [])
    const paginationMeta = computed(() => data.value?.meta)

    return { attachments, paginationMeta, isPending, isError }
}
