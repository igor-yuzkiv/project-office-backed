import { type MaybeRefOrGetter, toValue } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { syncTaskProjectDocumentsRequest } from '../api'
import { TaskProjectDocumentQueryKey } from '../config'
import { ProjectDocumentQueryKey } from '@/entities/project-document/config'

export function useSyncTaskProjectDocumentsMutation(taskId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (documentIds: string[]) => syncTaskProjectDocumentsRequest(toValue(taskId), documentIds),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
            queryClient.invalidateQueries({ queryKey: TaskProjectDocumentQueryKey.taskProjectDocuments(taskId) })
        },
    })
}
