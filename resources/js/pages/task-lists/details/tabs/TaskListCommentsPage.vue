<script setup lang="ts">
import { computed, ref, toValue } from 'vue'
import { useRouteParams } from '@vueuse/router'
import { useTaskListCommentsQuery } from '@/entities/task-list/queries'
import { useUpsertTaskListComment } from '@/entities/task-list/composables'
import { uploadTaskListAttachmentRequest } from '@/entities/task-list/api'
import { TaskListAttachmentRoles } from '@/entities/task-list/config'
import { useDeleteCommentMutation } from '@/entities/comment'
import { PAGE_SIZE } from '@/app/config'
import { CommentThread } from '@/widgets/comments/comment-thread'

const taskListId = useRouteParams<string>('id')

const page = ref(1)

const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { comments, paginationMeta, isPending } = useTaskListCommentsQuery(taskListId, pagination)
const { upsert } = useUpsertTaskListComment(taskListId)
const { mutateWithConfirm: deleteComment } = useDeleteCommentMutation()

function handleCreateComment(content: string) {
    upsert({ mode: 'create', content })
}

function handleUpdateComment(value: { commentId: string; content: string }) {
    upsert({ ...value, mode: 'edit' })
}

async function handleCommentImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) =>
            uploadTaskListAttachmentRequest(toValue(taskListId), file, TaskListAttachmentRoles.COMMENTS)
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
        class="app-content-background"
        @create="handleCreateComment"
        @update="handleUpdateComment"
        @delete="deleteComment"
    />
</template>
