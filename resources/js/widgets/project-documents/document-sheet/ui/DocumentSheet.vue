<script setup lang="ts">
import { onScopeDispose, ref, shallowRef, watch } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import { MarkdownCatalog, MarkdownPreview } from '@/shared/components/md-editor'
import { moveClass } from '@/shared/utils/dom-class.util'
import type { DomBlock, DomBlocks } from '@/shared/utils/markdown-anchor.dom.util'
import { useDocumentBlocks } from '../composables/use.document-blocks'

const BLOCK_CLASS = {
    hovered: 'document-block-hovered',
    selected: 'document-block-selected',
} as const

// The sheet renders a document and reports which block the reader pointed at. What a picked
// block is then used for belongs to whoever hosts it, which is why the toolbar is a slot.
const props = defineProps<{
    content: string
    blocksPickable?: boolean
    selectedBlock?: DomBlock | null
}>()

const emit = defineEmits<{
    (e: 'blocks-changed', blocks: DomBlocks): void
    (e: 'pick-block', block: DomBlock | null, event: MouseEvent): void
}>()

const previewRef = ref<InstanceType<typeof MarkdownPreview>>()
const catalogPopover = ref<InstanceType<typeof Popover>>()

const { blocks, refresh, findBlockAt } = useDocumentBlocks(() => previewRef.value?.getPreviewRoot() ?? null)

// shallowRef: this holds a live DOM node, and a deep ref would wrap it in a reactive proxy.
const hoveredBlock = shallowRef<DomBlock | null>(null)

watch(blocks, (current) => emit('blocks-changed', current), { immediate: true })

// immediate, so a selection that outlived a previous mount is decorated on the way in.
watch(
    () => props.selectedBlock ?? null,
    (block, previous) => moveClass(BLOCK_CLASS.selected, previous?.element ?? null, block?.element ?? null),
    { immediate: true }
)

function setHovered(block: DomBlock | null) {
    moveClass(BLOCK_CLASS.hovered, hoveredBlock.value?.element ?? null, block?.element ?? null)
    hoveredBlock.value = block
}

function handleMouseOver(event: MouseEvent) {
    if (!props.blocksPickable) return

    setHovered(findBlockAt(event.target))
}

function handleMouseLeave() {
    setHovered(null)
}

// A block left under the cursor keeps its tint until something clears it, and turning
// picking off is exactly such a moment.
watch(
    () => props.blocksPickable,
    (pickable) => {
        if (!pickable) setHovered(null)
    }
)

// Reported even when the click missed every block: the host may still care, and only it
// knows whether a click that hit nothing should suppress a link.
function handleClick(event: MouseEvent) {
    if (!props.blocksPickable) return

    emit('pick-block', findBlockAt(event.target), event)
}

onScopeDispose(() => {
    setHovered(null)
    moveClass(BLOCK_CLASS.selected, props.selectedBlock?.element ?? null, null)
})
</script>

<template>
    <div class="gap-3 p-6 document-canvas min-h-0 flex flex-1 flex-col items-center overflow-y-auto">
        <div class="gap-6 max-w-7xl flex w-full items-start">
            <div class="gap-3 min-w-0 flex flex-1 flex-col">
                <!-- The catalog is wanted rarely, so it waits behind a button instead
                     of holding a column beside the sheet. -->
                <div class="gap-2 flex items-center">
                    <Button
                        label="Contents"
                        size="small"
                        text
                        severity="secondary"
                        @click="catalogPopover?.toggle($event)"
                    >
                        <template #icon><Icon icon="heroicons:list-bullet" class="mr-1 text-base" /></template>
                    </Button>

                    <div class="ml-auto flex items-center">
                        <slot name="toolbar" />
                    </div>
                </div>

                <Popover ref="catalogPopover">
                    <MarkdownCatalog
                        v-if="previewRef"
                        class="w-64 text-sm max-h-[60vh] overflow-y-auto"
                        :catalog="previewRef.catalog"
                    />
                </Popover>

                <slot name="banner" />

                <div
                    class="p-10 rounded-xl bg-white dark:bg-surface-900 border-surface-200 dark:border-surface-700 document-sheet shadow-sm relative border"
                    @mouseover="handleMouseOver"
                    @mouseleave="handleMouseLeave"
                    @click="handleClick"
                >
                    <MarkdownPreview ref="previewRef" :model-value="content" @html-changed="refresh" />
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Not scoped: the markdown is rendered through v-html, so scoped attributes never reach it. -->
<style>
/* The document reads as a sheet, so the surface behind it is a drafting canvas. */
.document-canvas {
    background-color: var(--p-surface-100);
    background-image: radial-gradient(circle, var(--p-surface-300) 1px, transparent 1px);
    background-size: 18px 18px;
}

.dark .document-canvas {
    background-color: var(--p-surface-950);
    background-image: radial-gradient(circle, var(--p-surface-800) 1px, transparent 1px);
}

/* md-editor-v3 sets word-break: break-all on the preview, which snaps words mid-syllable.
   Long unbreakable tokens (urls, paths) still wrap, ordinary prose no longer does. */
.document-sheet .md-editor-preview,
.document-sheet .md-editor-preview :is(h1, h2, h3, h4, h5, h6) {
    word-break: normal;
    overflow-wrap: anywhere;
}

/* A code block or a wide table scrolls inside the sheet rather than widening it. Without this the
   sheet grows to fit its widest line and takes the whole layout with it. */
.document-sheet .md-editor-preview :is(pre, table) {
    max-width: 100%;
    overflow-x: auto;
}

/* md-editor-v3 gives the sticky code-block header z-index: 10000, which lands it above dialogs.
   Its own selector is three classes deep, so the override needs the sheet class to outrank it. */
.document-sheet .md-editor-preview .md-editor-code .md-editor-code-head {
    z-index: 1;
}

.md-editor-preview .document-block-hovered {
    background-color: color-mix(in srgb, var(--p-primary-color) 10%, transparent);
    border-radius: 0.25rem;
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--p-primary-color) 10%, transparent);
    cursor: pointer;
}

/* Deliberately a different hue from the hover tint: one says "you can pick this", the
   other says "this is what the host is about to write to". */
.md-editor-preview .document-block-selected {
    background-color: color-mix(in srgb, #f59e0b 18%, transparent);
    border-radius: 0.25rem;
    box-shadow: 0 0 0 4px color-mix(in srgb, #f59e0b 18%, transparent);
    outline: 2px solid #f59e0b;
    outline-offset: 2px;
}
</style>
