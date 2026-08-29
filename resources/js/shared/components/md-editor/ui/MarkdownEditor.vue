<script setup lang="ts">
import { computed } from 'vue'
import { MdEditor } from 'md-editor-v3'
import type { ToolbarNames } from 'md-editor-v3'
import { DEFAULT_TOOLBARS } from '../editor-toolbars'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

const props = withDefaults(
    defineProps<{
        preview?: boolean
        toolbars?: ToolbarNames[]
        /** CSS length. The editor grows with its content from here; `height: 100%` in `style` still bounds it. */
        minHeight?: string
        handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void
    }>(),
    { preview: false, toolbars: () => DEFAULT_TOOLBARS, minHeight: '300px' }
)

const emit = defineEmits<{
    /** Ctrl/Cmd+S inside the editor. The html arrives once the preview has rendered it. */
    (e: 'save', markdown: string, html: Promise<string>): void
}>()

const modelValue = defineModel<string>({ required: true })

const themeStore = useAppThemeStore()
const editorTheme = computed(() => (themeStore.isDark ? 'dark' : 'light'))

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
        :style="{ minHeight }"
        @on-upload-img="handleUploadImages"
        @on-save="(markdown: string, html: Promise<string>) => emit('save', markdown, html)"
    />
</template>
