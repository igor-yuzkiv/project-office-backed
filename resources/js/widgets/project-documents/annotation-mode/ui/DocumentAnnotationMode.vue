<script setup lang="ts">
import { computed, onScopeDispose, ref, shallowRef, watch } from 'vue'
import { onKeyStroke } from '@vueuse/core'
import Button from 'primevue/button'
import type { IAnnotation } from '@/entities/annotation'
import { useProjectDocumentAnnotationsQuery } from '@/entities/project-document'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { useToast } from '@/shared/composables/use.toast'
import type { DomBlock } from '@/shared/utils/markdown-anchor.dom.util'
import { buildAnchor, buildTextSnapshot } from '@/shared/utils/markdown-anchor.util'
import AnnotationComposer from './AnnotationComposer.vue'
import AnnotationSidebar from './AnnotationSidebar.vue'
import { useAnnotationAnchors } from '../composables/use.annotation-anchors'
import { useAnnotationBlocks } from '../composables/use.annotation-blocks'
import { useAnnotationEditor } from '../composables/use.annotation-editor'

const HOVERED_CLASS = 'annotation-hovered'
const SELECTED_CLASS = 'annotation-selected'

const props = defineProps<{ documentId: string; content: string }>()

const authStore = useAuthStore()
const toast = useToast()

const previewRef = ref<InstanceType<typeof MarkdownPreview>>()

// shallowRef: these hold live DOM nodes, and a deep ref would wrap them in reactive proxies.
const hoveredBlock = shallowRef<DomBlock | null>(null)
const selectedBlock = shallowRef<DomBlock | null>(null)

const draft = ref('')
const editing = ref<IAnnotation | null>(null)
const reanchoring = ref<IAnnotation | null>(null)

const { annotations, isPending, isError, refetch } = useProjectDocumentAnnotationsQuery(() => props.documentId)
const { blocks, refresh, findBlockAt } = useAnnotationBlocks(() => previewRef.value?.getPreviewRoot() ?? null)
const { orderedAnchors } = useAnnotationAnchors(annotations, blocks)
const { create, update, remove, isSaving, isUpdating } = useAnnotationEditor(() => props.documentId)

const isBusy = computed(() => isSaving.value || isUpdating.value)
const isReanchoring = computed(() => reanchoring.value !== null)
const isComposing = computed(() => editing.value !== null || selectedBlock.value !== null)

const BLOCK_LABELS: Record<string, string> = {
    p: 'Paragraph',
    li: 'List item',
    pre: 'Code',
    blockquote: 'Quote',
    table: 'Table',
}

const composerLabel = computed(() => {
    const tag = selectedBlock.value?.descriptor.tag ?? editing.value?.anchor.tag ?? ''

    return BLOCK_LABELS[tag] ?? (/^h[1-6]$/.test(tag) ? 'Heading' : tag)
})

function decorate(block: DomBlock | null, className: string, previous: DomBlock | null) {
    previous?.element.classList.remove(className)
    block?.element.classList.add(className)
}

function setHovered(block: DomBlock | null) {
    if (hoveredBlock.value?.element === block?.element) return

    decorate(block, HOVERED_CLASS, hoveredBlock.value)
    hoveredBlock.value = block
}

function setSelected(block: DomBlock | null) {
    if (selectedBlock.value?.element === block?.element) return

    decorate(block, SELECTED_CLASS, selectedBlock.value)
    selectedBlock.value = block
}

function handleMouseOver(event: MouseEvent) {
    setHovered(findBlockAt(event.target))
}

function handleMouseLeave() {
    setHovered(null)
}

function clearDraft() {
    draft.value = ''
    editing.value = null
}

async function handleClick(event: MouseEvent) {
    const annotation = reanchoring.value

    if (!annotation) {
        const block = findBlockAt(event.target)

        if (block) {
            setSelected(block)
            clearDraft()
        }

        return
    }

    // A block may contain a link, and picking it must not navigate away mid-request.
    event.preventDefault()

    if (isUpdating.value) return

    const block = findBlockAt(event.target)

    if (!block) return

    const saved = await update(annotation.id, {
        content: annotation.content,
        text_snapshot: buildTextSnapshot(block.descriptor.text),
        anchor: buildAnchor(block.descriptor),
    })

    if (saved) {
        reanchoring.value = null
        setSelected(block)
    }
}

async function save() {
    if (!draft.value.trim() || isBusy.value) return

    const target = editing.value

    if (target) {
        const saved = await update(target.id, {
            content: draft.value,
            text_snapshot: target.text_snapshot,
            anchor: target.anchor,
        })

        if (saved) clearDraft()

        return
    }

    const block = selectedBlock.value

    if (!block) return

    const saved = await create({
        content: draft.value,
        text_snapshot: buildTextSnapshot(block.descriptor.text),
        anchor: buildAnchor(block.descriptor),
    })

    if (saved) clearDraft()
}

function cancel() {
    clearDraft()
    setSelected(null)
}

function scrollTo(element: HTMLElement) {
    element.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

function selectAnnotation(annotation: IAnnotation) {
    const anchor = orderedAnchors.value.find((item) => item.annotation.id === annotation.id)
    const block = anchor?.element ? blocks.value.find((item) => item.element === anchor.element) : null

    if (!block) {
        toast.info('This annotation has lost its block. Use Re-anchor to attach it again.')
        setSelected(null)

        return null
    }

    setSelected(block)
    scrollTo(block.element)

    return block
}

function editAnnotation(annotation: IAnnotation) {
    if (!selectAnnotation(annotation)) return

    editing.value = annotation
    draft.value = annotation.content
}

function startReanchoring(annotation: IAnnotation) {
    reanchoring.value = annotation
    clearDraft()
}

function cancelReanchoring() {
    reanchoring.value = null
}

onKeyStroke('Escape', () => {
    if (isReanchoring.value) cancelReanchoring()
    else cancel()
})

watch(() => props.content, refresh)

onScopeDispose(() => {
    setHovered(null)
    setSelected(null)
})
</script>

<template>
    <div class="min-h-0 flex flex-1 overflow-hidden">
        <div class="min-h-0 flex flex-1 flex-col">
            <div class="gap-3 p-6 annotation-canvas min-h-0 flex flex-1 flex-col items-center overflow-y-auto">
                <div class="gap-3 max-w-5xl flex w-full flex-col">
                    <div
                        v-if="isReanchoring"
                        class="gap-3 rounded-lg p-3 bg-primary-50 dark:bg-primary-950 flex items-center justify-between"
                    >
                        <span class="text-sm text-surface-700 dark:text-surface-200">
                            Select the block this annotation belongs to.
                        </span>
                        <Button label="Cancel" severity="secondary" size="small" @click="cancelReanchoring" />
                    </div>

                    <p v-else class="text-xs text-surface-500">Click a block of the document to comment on it.</p>

                    <div
                        class="p-10 rounded-xl bg-white dark:bg-surface-900 border-surface-200 dark:border-surface-700 annotation-sheet shadow-sm relative border"
                        :class="{ 'annotation-picking': isReanchoring }"
                        @mouseover="handleMouseOver"
                        @mouseleave="handleMouseLeave"
                        @click="handleClick"
                    >
                        <MarkdownPreview ref="previewRef" :model-value="content" @html-changed="refresh" />
                    </div>
                </div>
            </div>

            <!-- Docked like a chat composer: it appears once a block is picked, and never covers the text. -->
            <AnnotationComposer
                v-if="isComposing"
                v-model:draft="draft"
                :block-label="composerLabel"
                :is-editing="editing !== null"
                :is-saving="isBusy"
                @save="save"
                @cancel="cancel"
            />
        </div>

        <AnnotationSidebar
            :anchors="orderedAnchors"
            :is-pending="isPending"
            :is-error="isError"
            :editing-id="editing?.id ?? null"
            :reanchoring-id="reanchoring?.id ?? null"
            :current-user-id="authStore.user?.id ?? null"
            @select="selectAnnotation"
            @edit="editAnnotation"
            @delete="remove($event.id)"
            @reanchor="startReanchoring"
            @retry="refetch()"
        />
    </div>
</template>

<!-- Not scoped: the markdown is rendered through v-html, so scoped attributes never reach it. -->
<style>
/* The document reads as a sheet, so the surface behind it is a drafting canvas. */
.annotation-canvas {
    background-color: var(--p-surface-100);
    background-image: radial-gradient(circle, var(--p-surface-300) 1px, transparent 1px);
    background-size: 18px 18px;
}

.dark .annotation-canvas {
    background-color: var(--p-surface-950);
    background-image: radial-gradient(circle, var(--p-surface-800) 1px, transparent 1px);
}

/* md-editor-v3 sets word-break: break-all on the preview, which snaps words mid-syllable.
   Long unbreakable tokens (urls, paths) still wrap, ordinary prose no longer does. */
.annotation-sheet .md-editor-preview,
.annotation-sheet .md-editor-preview :is(h1, h2, h3, h4, h5, h6) {
    word-break: normal;
    overflow-wrap: anywhere;
}

/* md-editor-v3 gives the sticky code-block header z-index: 10000, which lands it above dialogs.
   Its own selector is three classes deep, so the override needs the sheet class to outrank it. */
.annotation-sheet .md-editor-preview .md-editor-code .md-editor-code-head {
    z-index: 1;
}

.md-editor-preview .annotation-anchored {
    border-left: 3px solid var(--p-primary-color);
    background-color: color-mix(in srgb, var(--p-primary-color) 8%, transparent);
    padding-left: 0.5rem;
}

.md-editor-preview .annotation-hovered {
    background-color: color-mix(in srgb, var(--p-primary-color) 10%, transparent);
    border-radius: 0.25rem;
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--p-primary-color) 10%, transparent);
    cursor: pointer;
}

/* Deliberately a different hue from the hover tint: one says "you can pick this", the
   other says "this is what the composer is about to write to". */
.md-editor-preview .annotation-selected {
    background-color: color-mix(in srgb, #f59e0b 18%, transparent);
    border-radius: 0.25rem;
    box-shadow: 0 0 0 4px color-mix(in srgb, #f59e0b 18%, transparent);
    outline: 2px solid #f59e0b;
    outline-offset: 2px;
}

.annotation-picking .md-editor-preview :is(p, h1, h2, h3, h4, h5, h6, li, blockquote, pre, table):hover {
    cursor: pointer;
    background-color: color-mix(in srgb, var(--p-primary-color) 14%, transparent);
}
</style>
