<script setup lang="ts">
import { computed, ref, toValue } from 'vue'
import Divider from 'primevue/divider'
import Paginator from 'primevue/paginator'
import { UserAvatar } from '@/widgets/user/user-avatar'
import {
    useProjectDocumentCommentsQuery,
    useUpsertProjectDocumentComment,
    uploadProjectDocumentAttachmentRequest,
    ProjectDocumentAttachmentRoles,
} from '@/entities/project-document'
import { useDeleteCommentMutation } from '@/entities/comment'
import { PAGE_SIZE } from '@/app/config'
import { useAuthStore } from '@/app/stores/use.auth.store'
import CommentInputForm from '@/widgets/comments/ui/CommentInputForm.vue'
import CommentItem from '@/widgets/comments/ui/CommentItem.vue'
import { useRouteParams } from '@vueuse/router'

const documentId = useRouteParams<string>('id')

const authStore = useAuthStore()
const page = ref(1)

const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { comments, paginationMeta, isPending } = useProjectDocumentCommentsQuery(documentId, pagination)
const { upsert } = useUpsertProjectDocumentComment(documentId)
const { mutateWithConfirm: deleteComment } = useDeleteCommentMutation()

const showPaginator = computed(() => paginationMeta.value && paginationMeta.value.last_page > 1)

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
}

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
    <div class="gap-4 p-4 app-content-background flex flex-col">
        <div class="gap-3 flex items-start">
            <UserAvatar
                :initials="authStore.user?.initials ?? ''"
                :avatar-url="authStore.user?.avatar_url"
                size="medium"
                class="mt-1 shrink-0"
            />
            <div class="min-w-0 flex-1">
                <CommentInputForm
                    mode="create"
                    :handle-image-upload="handleCommentImageUpload"
                    @submit="handleCreateComment"
                />
            </div>
        </div>

        <Divider />

        <div v-if="isPending" class="text-surface-400 text-sm">Loading comments...</div>

        <div v-else-if="comments.length === 0" class="text-surface-400 text-sm">No comments yet.</div>

        <div v-else class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y">
            <CommentItem
                v-for="comment in comments"
                :key="comment.id"
                :comment="comment"
                @update="handleUpdateComment"
                @delete="deleteComment"
            />
        </div>

        <Paginator
            v-if="showPaginator"
            :rows="PAGE_SIZE"
            :total-records="paginationMeta!.total"
            :first="(page - 1) * PAGE_SIZE"
            @page="onPageChange"
        />
    </div>
</template>
