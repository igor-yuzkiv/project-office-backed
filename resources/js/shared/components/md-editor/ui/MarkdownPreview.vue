<script setup lang="ts">
import { computed } from 'vue'
import { MdPreview } from 'md-editor-v3'
import 'md-editor-v3/lib/preview.css'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

defineProps<{ modelValue: string }>()

const themeStore = useAppThemeStore()
const previewTheme = computed(() => (themeStore.isDark ? 'dark' : 'light'))
</script>

<template>
    <MdPreview
        :model-value="modelValue"
        language="en-US"
        :theme="previewTheme"
        :code-foldable="false"
        preview-theme="github"
        code-theme="github"
    />
</template>
