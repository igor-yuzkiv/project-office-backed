<script setup lang="ts">
import { computed, onMounted, ref, useId } from 'vue'
import { MdCatalog, MdPreview } from 'md-editor-v3'
import type { HeadList } from 'md-editor-v3'
import 'md-editor-v3/lib/preview.css'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

withDefaults(defineProps<{ modelValue: string; showCatalog?: boolean }>(), {
    showCatalog: false,
})

const themeStore = useAppThemeStore()
const editorId = useId()

const rootRef = ref<HTMLElement>()
const catalogScrollElement = ref<HTMLElement>()
const catalogHeadings = ref<HeadList[]>([])

const previewTheme = computed(() => (themeStore.isDark ? 'dark' : 'light'))
const hasCatalogHeadings = computed(() => catalogHeadings.value.length > 0)

onMounted(() => {
    // MdCatalog's click-to-scroll and MdPreview's own preview-wrapper both default to
    // scrolling themselves, but the page actually scrolls in an `overflow-auto` ancestor
    // set up by the tab layout — find it so clicking a catalog entry scrolls the right box.
    // Falls back to the document itself when no such ancestor exists (page-level scroll).
    let node = rootRef.value?.parentElement
    while (node) {
        if (['auto', 'scroll'].includes(getComputedStyle(node).overflowY)) {
            catalogScrollElement.value = node
            break
        }
        node = node.parentElement
    }
    catalogScrollElement.value ??= document.documentElement
})
</script>

<template>
    <div ref="rootRef">
        <MdPreview
            :editor-id="editorId"
            :model-value="modelValue"
            language="en-US"
            :theme="previewTheme"
            :code-foldable="false"
            preview-theme="github"
            @on-get-catalog="(list) => (catalogHeadings = list)"
        />
        <!-- code-theme="github" -->
        <MdCatalog
            v-if="showCatalog && catalogScrollElement && hasCatalogHeadings"
            class="fixed top-1/2 right-8 z-10 max-h-[70vh] w-56 -translate-y-1/2 overflow-y-auto rounded-lg border border-surface-200 bg-white p-4 shadow-lg dark:border-surface-700 dark:bg-surface-900"
            :editor-id="editorId"
            :theme="previewTheme"
            :scroll-element="catalogScrollElement"
        />
    </div>
</template>
