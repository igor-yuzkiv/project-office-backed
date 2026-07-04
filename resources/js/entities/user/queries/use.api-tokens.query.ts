import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchApiTokensRequest } from '../api'
import { ApiTokenQueryKey } from '../config'

export function useApiTokensQuery() {
    const { data, isPending, isError } = useQuery({
        queryKey: ApiTokenQueryKey.all,
        queryFn: () => fetchApiTokensRequest(),
    })

    const tokens = computed(() => data.value?.data ?? [])

    return { tokens, isPending, isError }
}
