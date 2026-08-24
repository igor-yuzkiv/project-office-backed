<script setup lang="ts">
import { computed, ref, toValue } from 'vue'
import {
    useProjectDocumentCommentsQuery,
    useUpsertProjectDocumentComment,
    uploadProjectDocumentAttachmentRequest,
    ProjectDocumentAttachmentRoles,
} from '@/entities/project-document'
import { useDeleteCommentMutation } from '@/entities/comment'
import { PAGE_SIZE } from '@/app/config'
import { CommentThread } from '@/widgets/comments'
import type { IProjectDocument } from '@/entities/project-document/types'

const props = defineProps<{
    document: IProjectDocument
}>()

const page = ref(1)

const documentId = computed(() => props.document.id)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { comments, paginationMeta, isPending } = useProjectDocumentCommentsQuery(documentId, pagination)
const { upsert } = useUpsertProjectDocumentComment(documentId)
const { mutateWithConfirm: deleteComment } = useDeleteCommentMutation()

function handleCreateComment(content: string) {
    upsert({ mode: 'create', content: content })
}

function handleUpdateComment(value: { commentId: string; content: string }) {
    upsert({ ...value, mode: 'edit' })
}

async function handleCommentImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) =>
            uploadProjectDocumentAttachmentRequest(toValue(documentId), file, ProjectDocumentAttachmentRoles.COMMENTS)
        )
    )
    callback(results.map((res) => res.data.url))
}
</script>

<template>
    <CommentThread
        v-model:page="page"
        :comments="comments"
        :pagination-meta="paginationMeta"
        :is-pending="isPending"
        :handle-image-upload="handleCommentImageUpload"
        class="overflow-auto"
        @create="handleCreateComment"
        @update="handleUpdateComment"
        @delete="deleteComment"
    />
</template>
