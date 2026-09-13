<script setup lang="ts">
import { computed } from 'vue'
import Divider from 'primevue/divider'
import Paginator from 'primevue/paginator'
import { PAGE_SIZE } from '@/app/config'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { UserAvatar } from '@/widgets/user/user-avatar'
import type { CommentKind, IComment } from '@/entities/comment'
import type { PaginationMeta } from '@/shared/types'
import CommentInputForm from './CommentInputForm.vue'
import CommentItem from './CommentItem.vue'

export type CommentKindFilter = CommentKind | 'all'

// The thread renders a comment list and reports what the reader did with it. Fetching, saving,
// and uploading stay with the entity that owns the comments, which is why they arrive as props.
const props = withDefaults(
    defineProps<{
        comments: IComment[]
        paginationMeta?: PaginationMeta
        isPending: boolean
        page: number
        handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void
        /** Where the new-comment form sits; a document reads top-down, so it ends with the form. */
        composerPlacement?: 'top' | 'bottom'
        /**
         * Bind it to show the All / Checkpoints / Comments segment. The filter narrows only the
         * comments already loaded — the pages stay as the server returned them.
         */
        kind?: CommentKindFilter
    }>(),
    { composerPlacement: 'top' }
)

const emit = defineEmits<{
    (e: 'create', content: string): void
    (e: 'update', value: { commentId: string; content: string }): void
    (e: 'delete', commentId: string): void
    (e: 'update:page', page: number): void
    (e: 'update:kind', kind: CommentKindFilter): void
}>()

const KIND_SEGMENTS: { value: CommentKindFilter; label: string }[] = [
    { value: 'all', label: 'All' },
    { value: 'checkpoint', label: 'Checkpoints' },
    { value: 'comment', label: 'Comments' },
]

const authStore = useAuthStore()

const hasKindFilter = computed(() => props.kind !== undefined)

const visibleComments = computed(() =>
    !props.kind || props.kind === 'all' ? props.comments : props.comments.filter((c) => c.kind === props.kind)
)

const emptyLabel = computed(() => {
    if (props.comments.length === 0) return 'No comments yet.'
    return props.kind === 'checkpoint' ? 'No checkpoints on this page.' : 'No comments on this page.'
})

const showPaginator = computed(() => props.paginationMeta && props.paginationMeta.last_page > 1)

function onPageChange(event: { page: number }) {
    emit('update:page', event.page + 1)
}
</script>

<template>
    <div class="gap-4 p-4 flex flex-col">
        <div
            v-if="hasKindFilter"
            class="gap-0.5 text-ink-2 flex items-center text-[13px]"
            role="group"
            aria-label="Show"
        >
            <button
                v-for="segment in KIND_SEGMENTS"
                :key="segment.value"
                type="button"
                class="px-2 py-0.5 hover:text-ink cursor-pointer rounded-[5px]"
                :class="{ 'bg-hover text-ink': kind === segment.value }"
                :aria-pressed="kind === segment.value"
                @click="emit('update:kind', segment.value)"
            >
                {{ segment.label }}
            </button>
        </div>

        <template v-if="composerPlacement === 'top'">
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
        </template>

        <div v-if="isPending" class="text-surface-400 text-sm">Loading comments...</div>

        <div v-else-if="visibleComments.length === 0" class="text-surface-400 text-sm">{{ emptyLabel }}</div>

        <div v-else class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y">
            <CommentItem
                v-for="comment in visibleComments"
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

        <template v-if="composerPlacement === 'bottom'">
            <Divider />

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
        </template>
    </div>
</template>
