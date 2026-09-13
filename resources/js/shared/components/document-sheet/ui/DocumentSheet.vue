<script setup lang="ts">
import { onScopeDispose, ref, shallowRef, watch } from 'vue'
import { MarkdownPreview } from '@/shared/components/md-editor'
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
</script>

<template>
    <!-- Prose straight on the page, no sheet of its own: the canvas is the page. -->
    <div class="type-prose" @mouseover="handleMouseOver" @mouseleave="handleMouseLeave" @click="handleClick">
        <MarkdownPreview ref="previewRef" :model-value="content" @html-changed="refresh" />
    </div>
</template>

<!-- Not scoped: the markdown is rendered through v-html, so scoped attributes never reach it. -->
<style>
.md-editor-preview .document-block-hovered {
    background-color: color-mix(in srgb, var(--color-accent) 10%, transparent);
    border-radius: 0.25rem;
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--color-accent) 10%, transparent);
    cursor: pointer;
}

/* Deliberately a different hue from the hover tint: one says "you can pick this", the
   other says "this is what the host is about to write to". */
.md-editor-preview .document-block-selected {
    background-color: color-mix(in srgb, var(--p-amber-500) 18%, transparent);
    border-radius: 0.25rem;
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--p-amber-500) 18%, transparent);
    outline: 2px solid var(--p-amber-500);
    outline-offset: 2px;
}
</style>
