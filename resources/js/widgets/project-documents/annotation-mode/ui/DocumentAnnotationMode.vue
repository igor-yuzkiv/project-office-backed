<script setup lang="ts">
import { computed, onScopeDispose, ref, shallowRef } from 'vue'
import { onKeyStroke } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import type { IAnnotation } from '@/entities/annotation'
import { useProjectDocumentAnnotationsQuery } from '@/entities/project-document'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { MarkdownCatalog, MarkdownPreview } from '@/shared/components/md-editor'
import { useToast } from '@/shared/composables/use.toast'
import type { DomBlock } from '@/shared/utils/markdown-anchor.dom.util'
import { buildAnchor, buildTextSnapshot } from '@/shared/utils/markdown-anchor.util'
import AnnotationComposer from './AnnotationComposer.vue'
import AnnotationSidebar from './AnnotationSidebar.vue'
import { ANNOTATION_CLASS, moveClass } from '../composables/annotation-decoration'
import { useAnnotationAnchors } from '../composables/use.annotation-anchors'
import { useAnnotationBlocks } from '../composables/use.annotation-blocks'
import { useAnnotationEditor } from '../composables/use.annotation-editor'

const props = defineProps<{ documentId: string; content: string }>()

const authStore = useAuthStore()
const toast = useToast()

const previewRef = ref<InstanceType<typeof MarkdownPreview>>()
const catalogPopover = ref<InstanceType<typeof Popover>>()

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
const isComposing = computed(() => selectedBlock.value !== null)

function setHovered(block: DomBlock | null) {
    moveClass(ANNOTATION_CLASS.hovered, hoveredBlock.value?.element ?? null, block?.element ?? null)
    hoveredBlock.value = block
}

function setSelected(block: DomBlock | null) {
    moveClass(ANNOTATION_CLASS.selected, selectedBlock.value?.element ?? null, block?.element ?? null)
    selectedBlock.value = block
}

function clearDraft() {
    draft.value = ''
    editing.value = null
}

function handleMouseOver(event: MouseEvent) {
    setHovered(findBlockAt(event.target))
}

function handleMouseLeave() {
    setHovered(null)
}

async function handleClick(event: MouseEvent) {
    const block = findBlockAt(event.target)
    const annotation = reanchoring.value

    if (!annotation) {
        if (block) {
            clearDraft()
            setSelected(block)
        }

        return
    }

    // A block may contain a link, and picking it must not navigate away mid-request.
    event.preventDefault()

    if (!block || isUpdating.value) return

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

/** Returns the block an annotation currently sits on, after scrolling it into view. */
function selectAnnotation(annotation: IAnnotation): DomBlock | null {
    const block = orderedAnchors.value.find((item) => item.annotation.id === annotation.id)?.block ?? null

    clearDraft()
    setSelected(block)

    if (!block) {
        toast.info('This annotation has lost its block. Use Re-anchor to attach it again.')

        return null
    }

    block.element.scrollIntoView({ behavior: 'smooth', block: 'center' })

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
    // The composer must go: while picking a target, Enter would write a new annotation instead.
    setSelected(null)
}

function cancelReanchoring() {
    reanchoring.value = null
}

onKeyStroke('Escape', () => (isReanchoring.value ? cancelReanchoring() : cancel()))

onScopeDispose(() => {
    setHovered(null)
    setSelected(null)
})
</script>

<template>
    <div class="min-h-0 flex flex-1 overflow-hidden">
        <div class="min-h-0 flex flex-1 flex-col">
            <div class="gap-3 p-6 annotation-canvas min-h-0 flex flex-1 flex-col items-center overflow-y-auto">
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
                        </div>

                        <Popover ref="catalogPopover">
                            <MarkdownCatalog
                                v-if="previewRef"
                                class="w-64 text-sm max-h-[60vh] overflow-y-auto"
                                :catalog="previewRef.catalog"
                            />
                        </Popover>

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
            </div>

            <!-- Docked like a chat composer: it appears once a block is picked, and never covers the text. -->
            <AnnotationComposer
                v-if="isComposing"
                :key="selectedBlock?.descriptor.index"
                v-model:draft="draft"
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
</style>
