<script setup lang="ts">
import { computed, ref, toValue } from 'vue'
import { useRouteParams } from '@vueuse/router'
import { useTaskCommentsQuery, useUpsertTaskComment } from '@/entities/task'
import { useDeleteCommentMutation } from '@/entities/comment'
import { PAGE_SIZE } from '@/app/config'
import { CommentThread } from '@/widgets/comments'
import { TaskAttachmentRoles } from '@/entities/task/config/task-attachment.config'
import { uploadTaskAttachmentRequest } from '@/entities/task/api/task-attachments.api'

const taskId = useRouteParams<string>('id')

const page = ref(1)

const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { comments, paginationMeta, isPending } = useTaskCommentsQuery(taskId, pagination)
const { upsert } = useUpsertTaskComment(taskId)
const { mutateWithConfirm: deleteComment } = useDeleteCommentMutation()

function handleCreateComment(content: string) {
    upsert({ mode: 'create', content: content })
}

function handleUpdateComment(value: { commentId: string; content: string }) {
    upsert({ ...value, mode: 'edit' })
}

async function handleCommentImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) => uploadTaskAttachmentRequest(toValue(taskId), file, TaskAttachmentRoles.COMMENTS))
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
        class="app-content-background"
        @create="handleCreateComment"
        @update="handleUpdateComment"
        @delete="deleteComment"
    />
</template>
