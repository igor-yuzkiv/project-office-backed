<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { useCreateTaskCommentMutation, useUpdateCommentMutation } from '@/entities/comment'
import type { AttachmentRole } from '@/entities/attachment/types'

const props = defineProps<{
    taskId: string
    mode: 'create' | 'edit'
    commentId?: string
    initialContent?: string
    image_entity_id?: string
    image_entity_type?: string
    image_role?: AttachmentRole
}>()

const emit = defineEmits<{
    cancel: []
    submitted: []
}>()

const content = ref(props.initialContent ?? '')

const createMutation = useCreateTaskCommentMutation(props.taskId)
const updateMutation = useUpdateCommentMutation()

const isPending = props.mode === 'create' ? createMutation.isPending : updateMutation.isPending

async function handleSubmit() {
    if (!content.value.trim()) return

    if (props.mode === 'create') {
        await createMutation.mutateAsync({ content: content.value })
        content.value = ''
    } else if (props.commentId) {
        await updateMutation.mutateAsync({ commentId: props.commentId, data: { content: content.value } })
    }

    emit('submitted')
}
</script>

<template>
    <div class="gap-3 flex flex-col">
        <MarkdownEditor
            v-model="content"
            :image_entity_id="image_entity_id"
            :image_entity_type="image_entity_type"
            :image_role="image_role"
        />
        <div class="gap-2 flex justify-end">
            <Button v-if="mode === 'edit'" label="Cancel" severity="secondary" size="small" @click="emit('cancel')" />
            <Button
                :label="mode === 'create' ? 'Add Comment' : 'Save'"
                size="small"
                :loading="isPending"
                :disabled="!content.trim()"
                @click="handleSubmit"
            />
        </div>
    </div>
</template>
