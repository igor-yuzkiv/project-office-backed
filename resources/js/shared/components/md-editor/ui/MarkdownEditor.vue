<script setup lang="ts">
import { computed } from 'vue'
import { MdEditor } from 'md-editor-v3'
import type { ToolbarNames } from 'md-editor-v3'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

const props = withDefaults(
    defineProps<{
        preview?: boolean
        handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void
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

function handleUploadImages(files: File[], callback: (urls: string[]) => void) {
    if (!files.length) return
    props.handleImageUpload?.(files, callback)
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
