<script setup lang="ts">
import { onScopeDispose, ref, shallowRef, watch } from 'vue'
import { ContentCard } from '@/shared/components/content-card'
import { MarkdownCatalog, MarkdownPreview } from '@/shared/components/md-editor'
import { moveClass } from '@/shared/utils/dom-class.util'
import type { DomBlock, DomBlocks } from '@/shared/utils/markdown-anchor.dom.util'
import { useDocumentBlocks } from '../composables/use.document-blocks'

const BLOCK_CLASS = {
    hovered: 'document-block-hovered',
    selected: 'document-block-selected',
} as const

// The sheet renders a document and reports which block the reader pointed at. What a picked
// block is then used for belongs to whoever hosts it.
const props = defineProps<{
    content: string
    showCatalog?: boolean
    blocksPickable?: boolean
    selectedBlock?: DomBlock | null
}>()

const emit = defineEmits<{
    (e: 'blocks-changed', blocks: DomBlocks): void
    (e: 'pick-block', block: DomBlock | null, event: MouseEvent): void
}>()

const previewRef = ref<InstanceType<typeof MarkdownPreview>>()

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

// Folded until asked for: the document is what the reader came for.
const catalogExpanded = ref(false)
</script>

<template>
    <!-- The catalog is its own card above the document, folded until asked for: the document is
         what the reader came for, and a long catalog would push it below the fold. -->
    <ContentCard
        v-if="showCatalog && previewRef?.catalog.hasHeadings"
        v-model:expanded="catalogExpanded"
        density="compact"
        expandable
    >
        <template #collapsed>
            <p class="text-surface-500 text-xs font-medium uppercase tracking-wide">Table of contents</p>
        </template>

        <p class="mb-3 text-surface-500 text-xs font-medium uppercase tracking-wide">Table of contents</p>
        <MarkdownCatalog class="text-sm" :catalog="previewRef.catalog" />
    </ContentCard>

    <ContentCard @mouseover="handleMouseOver" @mouseleave="handleMouseLeave" @click="handleClick">
        <MarkdownPreview ref="previewRef" :model-value="content" @html-changed="refresh" />
    </ContentCard>
</template>

<!-- Not scoped: the markdown is rendered through v-html, so scoped attributes never reach it. -->
<style>
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
