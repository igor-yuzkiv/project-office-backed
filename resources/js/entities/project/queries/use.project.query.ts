import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchProjectRequest } from '../api'
import { ProjectQueryKey } from '../config'

export function useProjectQuery(id: MaybeRefOrGetter<string>) {
    const { data, isPending, isError } = useQuery({
        queryKey: ProjectQueryKey.detail(id),
        queryFn: () => fetchProjectRequest(toValue(id)),
    })

    const project = computed(() => data.value?.data)

    return { project, isPending, isError }
}
