<script setup lang="ts">
import { MdEditor } from 'md-editor-v3'
import type { ToolbarNames } from 'md-editor-v3'
import 'md-editor-v3/lib/style.css'

withDefaults(defineProps<{ preview?: boolean }>(), { preview: true })

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
    />
</template>
