<script setup lang="ts">
import { MdPreview } from 'md-editor-v3'
import 'md-editor-v3/lib/preview.css'

defineProps<{ modelValue: string }>()
</script>

<template>
    <MdPreview
        :model-value="modelValue"
        language="en-US"
        :code-foldable="false"
        preview-theme="github"
        code-theme="github"
    />
</template>
