<script setup lang="ts">
import { computed } from 'vue'
import { MdEditor } from 'md-editor-v3'
import type { ToolbarNames } from 'md-editor-v3'
import { uploadAttachmentRequest } from '@/entities/attachment/api'
import type { AttachmentRole } from '@/entities/attachment/types'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

const props = withDefaults(
    defineProps<{
        preview?: boolean
        image_entity_type?: string
        image_entity_id?: string
        image_role?: AttachmentRole
    }>(),
    { preview: false }
)

const modelValue = defineModel<string>({ required: true })

const themeStore = useAppThemeStore()
const editorTheme = computed(() => (themeStore.isDark ? 'dark' : 'light'))

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
    'image',
    '-',
    'revoke',
    'next',
    '=',
    'catalog',
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
        :theme="editorTheme"
        :preview="preview"
        :toolbars="toolbars"
        preview-theme="github"
        code-theme="github"
        :code-foldable="false"
        style="min-height: 300px"
        @on-upload-img="handleUploadImages"
    />
</template>
