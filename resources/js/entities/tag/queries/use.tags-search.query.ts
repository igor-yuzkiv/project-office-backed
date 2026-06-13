import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { searchTagsRequest } from '../api'
import { TagQueryKey } from '../config'

export function useTagsSearchQuery(query: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: TagQueryKey.search(query),
        queryFn: () => searchTagsRequest(toValue(query)),
    })

    const tags = computed(() => data.value?.data ?? [])

    return { tags, isPending, isError }
}
