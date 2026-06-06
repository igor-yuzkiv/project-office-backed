<script setup lang="ts">
import { MdEditor } from 'md-editor-v3'
import type { ToolbarNames } from 'md-editor-v3'
import 'md-editor-v3/lib/style.css'
import { uploadAttachmentRequest } from '@/entities/attachment/api'

const props = withDefaults(
    defineProps<{
        preview?: boolean
        image_entity_type?: string
        image_entity_id?: string
        image_role?: string
    }>(),
    { preview: false }
)

const modelValue = defineModel<string>({ required: true })

const toolbars: ToolbarNames[] = [
    'bold',
    'underline',
    'italic',
    '-',
    'strikeThrough',
    'title',
    'sub',
    'sup',
    'quote',
    'unorderedList',
    'orderedList',
    'task',
    '-',
    'codeRow',
    'code',
    'link',
    'table',
    '-',
    'revoke',
    'next',
    '=',
    'preview',
    'previewOnly',
    'pageFullscreen',
    'fullscreen',
]

async function handleUploadImages(files: File[], callback: (urls: string[]) => void) {
    if (!files.length) return

    const responses = await Promise.all(
        files.map((file) =>
            uploadAttachmentRequest({
                file,
                entity_type: props.image_entity_type,
                entity_id: props.image_entity_id,
                role: props.image_role,
            })
        )
    )

    callback(responses.map((res) => res.data.url))
}
</script>

<template>
    <MdEditor
        v-model="modelValue"
        language="en-US"
        :preview="preview"
        :toolbars="toolbars"
        preview-theme="github"
        code-theme="github"
        :code-foldable="false"
        style="min-height: 300px"
        @on-upload-img="handleUploadImages"
    />
</template>
