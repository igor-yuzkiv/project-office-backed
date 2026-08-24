<script setup lang="ts">
import { computed } from 'vue'
import Divider from 'primevue/divider'
import Paginator from 'primevue/paginator'
import { PAGE_SIZE } from '@/app/config'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { UserAvatar } from '@/widgets/user/user-avatar'
import type { IComment } from '@/entities/comment'
import type { PaginationMeta } from '@/shared/types'
import CommentInputForm from './CommentInputForm.vue'
import CommentItem from './CommentItem.vue'

// The thread renders a comment list and reports what the reader did with it. Fetching, saving,
// and uploading stay with the entity that owns the comments, which is why they arrive as props.
const props = defineProps<{
    comments: IComment[]
    paginationMeta?: PaginationMeta
    isPending: boolean
    page: number
    handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void
}>()

const emit = defineEmits<{
    (e: 'create', content: string): void
    (e: 'update', value: { commentId: string; content: string }): void
    (e: 'delete', commentId: string): void
    (e: 'update:page', page: number): void
}>()

const authStore = useAuthStore()

const showPaginator = computed(() => props.paginationMeta && props.paginationMeta.last_page > 1)

function onPageChange(event: { page: number }) {
    emit('update:page', event.page + 1)
}
</script>

<template>
    <div class="gap-4 p-4 flex flex-col">
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
                    :handle-image-upload="handleImageUpload"
                    @submit="emit('create', $event)"
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
                @update="emit('update', $event)"
                @delete="emit('delete', $event)"
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
