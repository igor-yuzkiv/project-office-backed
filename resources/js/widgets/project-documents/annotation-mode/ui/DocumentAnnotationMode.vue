<script setup lang="ts">
import { computed, nextTick, onScopeDispose, ref, shallowRef, watch } from 'vue'
import { onKeyStroke } from '@vueuse/core'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import Textarea from 'primevue/textarea'
import type { IAnnotation } from '@/entities/annotation'
import { useProjectDocumentAnnotationsQuery } from '@/entities/project-document'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { useToast } from '@/shared/composables/use.toast'
import type { DomBlock } from '@/shared/utils/markdown-anchor.dom.util'
import { buildAnchor, buildTextSnapshot } from '@/shared/utils/markdown-anchor.util'
import AnnotationSidebar from './AnnotationSidebar.vue'
import { useAnnotationAnchors, type AnnotationAnchor } from '../composables/use.annotation-anchors'
import { useAnnotationBlocks } from '../composables/use.annotation-blocks'
import { useAnnotationEditor } from '../composables/use.annotation-editor'

const HIGHLIGHT_CLASS = 'annotation-highlighted'
const HOVERED_CLASS = 'annotation-hovered'
const HIGHLIGHT_DURATION = 2500

const props = defineProps<{ documentId: string; content: string }>()

const authStore = useAuthStore()
const toast = useToast()

const previewRef = ref<InstanceType<typeof MarkdownPreview>>()
const containerRef = ref<HTMLElement>()
const popover = ref<InstanceType<typeof Popover>>()

// shallowRef for the same reason as in useAnnotationBlocks: these hold live DOM nodes.
const hoveredBlock = shallowRef<DomBlock | null>(null)
const activeBlock = shallowRef<DomBlock | null>(null)
const draft = ref('')
const editingId = ref<string | null>(null)
const activeId = ref<string | null>(null)
const reanchoring = ref<IAnnotation | null>(null)

const { annotations, isPending, isError, refetch } = useProjectDocumentAnnotationsQuery(() => props.documentId)
const { blocks, refresh, findBlockAt } = useAnnotationBlocks(() => previewRef.value?.getPreviewRoot() ?? null)
const { orderedAnchors, annotationsOf } = useAnnotationAnchors(annotations, blocks)
const { create, update, remove, isSaving, isUpdating } = useAnnotationEditor(() => props.documentId)

const isBusy = computed(() => isSaving.value || isUpdating.value)
const isReanchoring = computed(() => reanchoring.value !== null)
const activeAnnotations = computed<IAnnotation[]>(() =>
    activeBlock.value ? annotationsOf(activeBlock.value.element) : []
)

const hoverButtonStyle = computed(() => {
    if (!hoveredBlock.value || !containerRef.value) return undefined

    const block = hoveredBlock.value.element.getBoundingClientRect()
    const container = containerRef.value.getBoundingClientRect()

    // Inside the block's top-right corner: outside it, the pointer would cross a gap that
    // belongs to no block, and the button would be gone before the click landed.
    return { top: `${block.top - container.top}px`, right: `${container.right - block.right}px` }
})

let highlighted: HTMLElement | null = null
let highlightTimer: ReturnType<typeof setTimeout> | undefined

function clearHighlight() {
    clearTimeout(highlightTimer)
    highlighted?.classList.remove(HIGHLIGHT_CLASS)
    highlighted = null
}

function highlight(element: HTMLElement) {
    clearHighlight()
    element.classList.add(HIGHLIGHT_CLASS)
    highlighted = element
    highlightTimer = setTimeout(clearHighlight, HIGHLIGHT_DURATION)
}

function setHovered(block: DomBlock | null) {
    if (hoveredBlock.value?.element === block?.element) return

    hoveredBlock.value?.element.classList.remove(HOVERED_CLASS)
    block?.element.classList.add(HOVERED_CLASS)
    hoveredBlock.value = block
}

function handleMouseOver(event: MouseEvent) {
    // The button sits on top of the block it belongs to, so pointing at it must not clear the block.
    if (event.target instanceof HTMLElement && event.target.closest('[data-annotation-trigger]')) return

    setHovered(findBlockAt(event.target))
}

function handleMouseLeave() {
    setHovered(null)
}

function openEditor(event: MouseEvent) {
    activeBlock.value = hoveredBlock.value
    draft.value = ''
    editingId.value = null
    popover.value?.toggle(event)
}

function startEditing(annotation: IAnnotation) {
    editingId.value = annotation.id
    draft.value = annotation.content
}

function cancel() {
    if (editingId.value) {
        editingId.value = null
        draft.value = ''

        return
    }

    popover.value?.hide()
}

async function save() {
    const block = activeBlock.value

    if (!block || !draft.value.trim() || isBusy.value) return

    const editing = editingId.value ? activeAnnotations.value.find((item) => item.id === editingId.value) : null

    const saved = editing
        ? await update(editing.id, {
              content: draft.value,
              text_snapshot: editing.text_snapshot,
              anchor: editing.anchor,
          })
        : await create({
              content: draft.value,
              text_snapshot: buildTextSnapshot(block.descriptor.text),
              anchor: buildAnchor(block.descriptor),
          })

    if (!saved) return

    draft.value = ''
    editingId.value = null
    popover.value?.hide()
}

function selectAnchor(anchor: AnnotationAnchor) {
    activeId.value = anchor.annotation.id

    if (!anchor.element) {
        toast.info('This annotation has lost its block. Use Re-anchor to attach it again.')

        return
    }

    anchor.element.scrollIntoView({ behavior: 'smooth', block: 'center' })
    highlight(anchor.element)
}

async function editFromSidebar(annotation: IAnnotation) {
    const anchor = orderedAnchors.value.find((item) => item.annotation.id === annotation.id)
    const block = anchor?.element ? blocks.value.find((item) => item.element === anchor.element) : null

    if (!block) {
        toast.info('This annotation has lost its block. Use Re-anchor to attach it again.')

        return
    }

    activeBlock.value = block
    startEditing(annotation)

    // The popover is positioned against the block in page coordinates, so the block has to be
    // on screen first — and instantly, because a smooth scroll would still be running.
    block.element.scrollIntoView({ block: 'center' })
    await nextTick()

    popover.value?.show(new Event('click'), block.element)
}

function startReanchoring(annotation: IAnnotation) {
    reanchoring.value = annotation
    popover.value?.hide()
}

function cancelReanchoring() {
    reanchoring.value = null
}

async function handleContainerClick(event: MouseEvent) {
    const annotation = reanchoring.value

    if (!annotation) return

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
        highlight(block.element)
    }
}

onKeyStroke('Escape', () => {
    if (isReanchoring.value) cancelReanchoring()
})

watch(() => props.content, refresh)

onScopeDispose(() => {
    clearHighlight()
    setHovered(null)
})
</script>

<template>
    <div class="gap-6 flex items-start justify-center">
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

            <p v-if="!isReanchoring" class="text-xs text-surface-500">
                Hover any block of the document to comment on it.
            </p>

            <div
                ref="containerRef"
                class="p-10 rounded-xl bg-white dark:bg-surface-900 border-surface-200 dark:border-surface-700 shadow-sm relative border"
                :class="{ 'annotation-picking': isReanchoring }"
                @mouseover="handleMouseOver"
                @mouseleave="handleMouseLeave"
                @click="handleContainerClick"
            >
                <MarkdownPreview ref="previewRef" :model-value="content" @html-changed="refresh" />

                <Button
                    v-if="hoveredBlock && !isReanchoring"
                    class="absolute z-10"
                    data-annotation-trigger
                    icon="pi pi-comment"
                    label="Add comment"
                    severity="secondary"
                    size="small"
                    :style="hoverButtonStyle"
                    @click="openEditor"
                />
            </div>
        </div>

        <AnnotationSidebar
            :anchors="orderedAnchors"
            :is-pending="isPending"
            :is-error="isError"
            :active-id="activeId"
            :reanchoring-id="reanchoring?.id ?? null"
            :current-user-id="authStore.user?.id ?? null"
            @select="selectAnchor"
            @edit="editFromSidebar"
            @delete="remove($event.id)"
            @reanchor="startReanchoring"
            @retry="refetch()"
        />
    </div>

    <Popover ref="popover">
        <div class="w-80 gap-3 flex flex-col">
            <div v-if="activeAnnotations.length" class="gap-3 max-h-60 flex flex-col overflow-y-auto">
                <div v-for="annotation in activeAnnotations" :key="annotation.id" class="gap-1 flex flex-col">
                    <span class="text-xs font-medium text-surface-500">{{ annotation.author.name }}</span>
                    <p class="text-sm text-surface-900 dark:text-surface-0 whitespace-pre-line">
                        {{ annotation.content }}
                    </p>
                    <div v-if="annotation.author.id === authStore.user?.id" class="gap-2 flex">
                        <Button label="Edit" severity="secondary" size="small" text @click="startEditing(annotation)" />
                        <Button label="Delete" severity="danger" size="small" text @click="remove(annotation.id)" />
                    </div>
                </div>
            </div>

            <Textarea v-model="draft" auto-resize placeholder="Add a comment" rows="3" />

            <div class="gap-2 flex justify-end">
                <Button label="Cancel" severity="secondary" size="small" text @click="cancel" />
                <Button label="Save" size="small" :disabled="!draft.trim() || isBusy" :loading="isBusy" @click="save" />
            </div>
        </div>
    </Popover>
</template>

<!-- Not scoped: the markdown is rendered through v-html, so scoped attributes never reach it. -->
<style>
.md-editor-preview .annotation-anchored {
    border-left: 3px solid var(--p-primary-color);
    background-color: color-mix(in srgb, var(--p-primary-color) 8%, transparent);
    padding-left: 0.5rem;
}

.md-editor-preview .annotation-hovered {
    background-color: color-mix(in srgb, var(--p-primary-color) 10%, transparent);
    border-radius: 0.25rem;
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--p-primary-color) 10%, transparent);
}

.md-editor-preview .annotation-highlighted {
    outline: 2px solid var(--p-primary-color);
    outline-offset: 2px;
    transition: outline-color 0.3s ease;
}

.annotation-picking .md-editor-preview :is(p, h1, h2, h3, h4, h5, h6, li, blockquote, pre, table):hover {
    cursor: pointer;
    background-color: color-mix(in srgb, var(--p-primary-color) 14%, transparent);
}
</style>
