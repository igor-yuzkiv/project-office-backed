<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { DisplayDate } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'
import type { IComment } from '@/entities/comment'
import CommentInputForm from './CommentInputForm.vue'

const props = defineProps<{ comment: IComment }>()

const emit = defineEmits<{
    (e: 'update', value: { commentId: string; content: string }): void
    (e: 'delete', commentId: string): void
}>()

const isEditing = ref(false)

function handleEditSubmit(content: string) {
    emit('update', { commentId: props.comment.id, content })
    isEditing.value = false
}

function handleDelete() {
    emit('delete', props.comment.id)
}
</script>

<template>
    <div class="gap-3 py-4 first:pt-0 flex">
        <UserAvatar
            :initials="comment.author.initials"
            :avatar-url="comment.author.avatar_url"
            size="medium"
            class="mt-0.5 shrink-0"
        />

        <div class="gap-2 min-w-0 flex flex-1 flex-col">
            <div class="gap-4 flex items-center justify-between">
                <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">
                    {{ comment.author.name }}
                </span>
                <div class="gap-1 flex shrink-0 items-center">
                    <DisplayDate :date="comment.created_at" class="text-xs text-surface-400 dark:text-surface-500" />
                    <Button
                        v-if="comment.can.update"
                        icon="pi pi-pencil"
                        text
                        rounded
                        size="small"
                        severity="secondary"
                        @click="isEditing = true"
                    />
                    <Button
                        v-if="comment.can.delete"
                        icon="pi pi-trash"
                        text
                        rounded
                        size="small"
                        severity="danger"
                        @click="handleDelete"
                    />
                </div>
            </div>

            <CommentInputForm
                v-if="isEditing"
                mode="edit"
                :comment-id="comment.id"
                :initial-content="comment.content"
                @submit="handleEditSubmit"
                @cancel="isEditing = false"
            />

            <MarkdownPreview v-else :model-value="comment.content" />
        </div>
    </div>
</template>
