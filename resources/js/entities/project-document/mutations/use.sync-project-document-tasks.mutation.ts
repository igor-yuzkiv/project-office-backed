import { type MaybeRefOrGetter, toValue } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { syncProjectDocumentTasksRequest } from '../api'
import { ProjectDocumentQueryKey, ProjectDocumentTaskQueryKey } from '../config'

export function useSyncProjectDocumentTasksMutation(documentId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (taskIds: string[]) => syncProjectDocumentTasksRequest(toValue(documentId), taskIds),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
            queryClient.invalidateQueries({ queryKey: ProjectDocumentTaskQueryKey.documentTasks(documentId) })
        },
    })
}
