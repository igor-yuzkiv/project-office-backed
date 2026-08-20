<script setup lang="ts">
import { MdCatalog } from 'md-editor-v3'

/** The shape MarkdownPreview exposes as `catalog`. */
export interface MarkdownCatalogProps {
    editorId: string
    theme: 'light' | 'dark'
    scrollElement: HTMLElement | undefined
    hasHeadings: boolean
}

defineProps<{ catalog: MarkdownCatalogProps }>()
</script>

<template>
    <MdCatalog
        v-if="catalog.hasHeadings"
        :editor-id="catalog.editorId"
        :scroll-element="catalog.scrollElement"
        :theme="catalog.theme"
    />
</template>
