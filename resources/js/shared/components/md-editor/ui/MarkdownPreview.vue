<script setup lang="ts">
import { computed, onMounted, ref, useId } from 'vue'
import { MdPreview } from 'md-editor-v3'
import type { HeadList } from 'md-editor-v3'
import 'md-editor-v3/lib/preview.css'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

defineProps<{ modelValue: string }>()

const emit = defineEmits<{
    (e: 'htmlChanged'): void
}>()

const themeStore = useAppThemeStore()
const editorId = useId()

const rootRef = ref<HTMLElement>()
const catalogScrollElement = ref<HTMLElement>()
const catalogHeadings = ref<HeadList[]>([])

const previewTheme = computed<'dark' | 'light'>(() => (themeStore.isDark ? 'dark' : 'light'))
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

// The rendered markdown lives in md-editor-v3's own container; consumers that decorate blocks
// need that element, not this wrapper.
function getPreviewRoot(): HTMLElement | null {
    return rootRef.value?.querySelector('.md-editor-preview') ?? null
}

// One handle instead of four internals: the consumer can render MarkdownCatalog next to the
// preview instead of over it, without knowing how the library is wired.
const catalog = computed(() => ({
    editorId,
    theme: previewTheme.value,
    scrollElement: catalogScrollElement.value,
    hasHeadings: hasCatalogHeadings.value,
}))

defineExpose({ getPreviewRoot, catalog })
</script>

<template>
    <div ref="rootRef" class="relative">
        <MdPreview
            :editor-id="editorId"
            :model-value="modelValue"
            language="en-US"
            :theme="previewTheme"
            :code-foldable="false"
            preview-theme="github"
            @on-get-catalog="(list) => (catalogHeadings = list)"
            @on-html-changed="() => emit('htmlChanged')"
        />
        <!-- code-theme="github" -->
    </div>
</template>
