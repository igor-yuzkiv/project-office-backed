import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { searchUsersRequest } from '../api'
import { UserQueryKey } from '../config'

export function useUsersSearchQuery(query: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: UserQueryKey.search(query),
        queryFn: () => searchUsersRequest(toValue(query)),
        enabled: () => toValue(query).length >= 1,
    })

    const users = computed(() => data.value?.data ?? [])

    return { users, isPending, isError }
}
