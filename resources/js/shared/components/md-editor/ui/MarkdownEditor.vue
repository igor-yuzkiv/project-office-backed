<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { MdEditor } from 'md-editor-v3'
import type { ExposeParam, ToolbarNames } from 'md-editor-v3'
import { DEFAULT_TOOLBARS } from '../editor-toolbars'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import MarkdownModeSegment from './MarkdownModeSegment.vue'
import type { MarkdownEditorMode } from './MarkdownModeSegment.vue'

const props = withDefaults(
    defineProps<{
        /** Start in Split (source next to the rendered text) instead of Write. */
        preview?: boolean
        toolbars?: ToolbarNames[]
        /** A host that shows the rendered text elsewhere (the document sheet) turns the segment off. */
        modeSegment?: boolean
        /** CSS length. The editor grows with its content from here; `height: 100%` in `style` still bounds it. */
        minHeight?: string
        handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void
    }>(),
    { preview: false, toolbars: () => DEFAULT_TOOLBARS, minHeight: '300px', modeSegment: true }
)

const emit = defineEmits<{
    /** Ctrl/Cmd+S inside the editor. */
    (e: 'save'): void
}>()

const modelValue = defineModel<string>({ required: true })

const themeStore = useAppThemeStore()
const editorTheme = computed(() => (themeStore.isDark ? 'dark' : 'light'))

const editorRef = ref<ExposeParam>()

// The Write / Preview / Split segment replaces the library's own preview buttons. It is the
// first (and only) `defToolbars` node, which md-editor-v3 addresses by index, and always sits
// at the right end of whatever toolbar the host passed.
const MODE_SEGMENT = 0
const toolbars = computed<ToolbarNames[]>(() =>
    props.modeSegment ? [...props.toolbars, MODE_SEGMENT] : props.toolbars
)

const mode = ref<MarkdownEditorMode>(props.preview ? 'split' : 'write')

// The library keeps two flags: `preview` (rendered pane shown) and `previewOnly` (source pane
// hidden). They are mirrored into `mode` only, never applied back, so the library's own
// changes reach the segment without the segment echoing them.
const previewShown = ref(props.preview)
const sourceHidden = ref(false)

onMounted(() => {
    editorRef.value?.on('preview', (status) => {
        previewShown.value = status
        mode.value = modeFromEditor()
    })
    editorRef.value?.on('previewOnly', (status) => {
        sourceHidden.value = status
        mode.value = modeFromEditor()
    })
})

function modeFromEditor(): MarkdownEditorMode {
    return sourceHidden.value ? 'preview' : previewShown.value ? 'split' : 'write'
}

function applyMode(next: MarkdownEditorMode) {
    mode.value = next
    const editor = editorRef.value
    if (!editor) return
    if (next === 'preview') {
        editor.togglePreviewOnly(true)
        return
    }
    // Leaving preview-only has to be said explicitly: togglePreview() alone hides the source pane
    // flag internally but never emits previewOnly=false, so the segment would stay on Preview.
    if (sourceHidden.value) editor.togglePreviewOnly(false)
    editor.togglePreview(next === 'split')
}

function handleUploadImages(files: File[], callback: (urls: string[]) => void) {
    if (!files.length) return
    props.handleImageUpload?.(files, callback)
}
</script>

<template>
    <MdEditor
        ref="editorRef"
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
        @on-save="emit('save')"
    >
        <template v-if="modeSegment" #defToolbars>
            <MarkdownModeSegment :model-value="mode" @update:model-value="applyMode" />
        </template>
    </MdEditor>
</template>
