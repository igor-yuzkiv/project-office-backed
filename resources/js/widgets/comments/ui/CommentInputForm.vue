<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import { MarkdownEditor } from '@/shared/components/md-editor'
import type { AttachmentRole } from '@/entities/attachment/types'

const props = defineProps<{
    mode: 'create' | 'edit'
    commentId?: string
    initialContent?: string
    image_entity_id?: string
    image_entity_type?: string
    image_role?: AttachmentRole
}>()

const emit = defineEmits<{
    submit: [content: string]
    cancel: []
}>()

const content = ref(props.initialContent ?? '')

function handleSubmit() {
    if (!content.value.trim()) return

    emit('submit', content.value)

    if (props.mode === 'create') {
        content.value = ''
    }
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
                :disabled="!content.trim()"
                @click="handleSubmit"
            />
        </div>
    </div>
</template>
