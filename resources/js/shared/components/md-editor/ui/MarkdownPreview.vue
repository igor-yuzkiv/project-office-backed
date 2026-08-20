<script setup lang="ts">
import { computed, onMounted, ref, useId } from 'vue'
import { MdCatalog, MdPreview } from 'md-editor-v3'
import type { HeadList } from 'md-editor-v3'
import 'md-editor-v3/lib/preview.css'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

withDefaults(defineProps<{ modelValue: string; showCatalog?: boolean }>(), {
    showCatalog: false,
})

const emit = defineEmits<{
    (e: 'htmlChanged', html: string): void
}>()

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

// The rendered markdown lives in md-editor-v3's own container; consumers that decorate blocks
// need that element, not this wrapper.
function getPreviewRoot(): HTMLElement | null {
    return rootRef.value?.querySelector('.md-editor-preview') ?? null
}

defineExpose({ getPreviewRoot })
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
            @on-html-changed="(html) => emit('htmlChanged', html)"
        />
        <!-- code-theme="github" -->
        <!--
            The catalog rides along the right edge of the description itself, not of the window:
            anchored to this block so it cannot reach whatever else the page puts beside it, and
            sticky inside that band so it stays in view while the description scrolls. The band
            ignores pointer events so it does not swallow clicks on the text underneath it.
        -->
        <div
            v-if="showCatalog && catalogScrollElement && hasCatalogHeadings"
            class="inset-y-0 right-0 w-56 pointer-events-none absolute"
        >
            <MdCatalog
                class="top-4 rounded-lg border-surface-200 bg-white p-4 shadow-lg dark:border-surface-700 dark:bg-surface-900 pointer-events-auto sticky max-h-[70vh] overflow-y-auto border opacity-60 transition-opacity hover:opacity-100"
                :editor-id="editorId"
                :theme="previewTheme"
                :scroll-element="catalogScrollElement"
            />
        </div>
    </div>
</template>
