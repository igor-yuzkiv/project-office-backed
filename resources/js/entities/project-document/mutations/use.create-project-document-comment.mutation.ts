import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { CreateCommentDto } from '@/entities/comment/types'
import { createProjectDocumentCommentRequest } from '../api'
import { ProjectDocumentCommentQueryKey } from '../config'

export function useCreateProjectDocumentCommentMutation(documentId: MaybeRefOrGetter<string>) {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: (data: CreateCommentDto) => createProjectDocumentCommentRequest(toValue(documentId), data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ProjectDocumentCommentQueryKey.documentComments(documentId) })
        },
    })
}
