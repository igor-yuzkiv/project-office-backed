<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import Textarea from 'primevue/textarea'
import type { IAnnotation } from '@/entities/annotation'
import { useProjectDocumentAnnotationsQuery } from '@/entities/project-document'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { MarkdownPreview } from '@/shared/components/md-editor'
import type { DomBlock } from '@/shared/utils/markdown-anchor.dom.util'
import { buildAnchor, buildTextSnapshot } from '@/shared/utils/markdown-anchor.util'
import { useAnnotationAnchors } from '../composables/use.annotation-anchors'
import { useAnnotationBlocks } from '../composables/use.annotation-blocks'
import { useAnnotationEditor } from '../composables/use.annotation-editor'

const props = defineProps<{ documentId: string; content: string }>()

const authStore = useAuthStore()

const previewRef = ref<InstanceType<typeof MarkdownPreview>>()
const containerRef = ref<HTMLElement>()
const popover = ref<InstanceType<typeof Popover>>()

const hoveredBlock = ref<DomBlock | null>(null)
const activeBlock = ref<DomBlock | null>(null)
const draft = ref('')
const editingId = ref<string | null>(null)

const { annotations, isPending, isError } = useProjectDocumentAnnotationsQuery(() => props.documentId)
const { blocks, refresh, findBlockAt } = useAnnotationBlocks(() => previewRef.value?.getPreviewRoot() ?? null)
const { anchors, annotationsOf } = useAnnotationAnchors(annotations, blocks)
const { create, update, remove, isSaving, isUpdating } = useAnnotationEditor(() => props.documentId)

const isBusy = computed(() => isSaving.value || isUpdating.value)
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

function isOwn(annotation: IAnnotation): boolean {
    return annotation.author.id === authStore.user?.id
}

function handleMouseOver(event: MouseEvent) {
    // The button sits on top of the block it belongs to, so pointing at it must not clear the block.
    if (event.target instanceof HTMLElement && event.target.closest('[data-annotation-trigger]')) return

    hoveredBlock.value = findBlockAt(event.target)
}

function handleMouseLeave() {
    hoveredBlock.value = null
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

watch(() => props.content, refresh)
</script>

<template>
    <div class="gap-4 flex items-start">
        <div ref="containerRef" class="relative flex-1" @mouseover="handleMouseOver" @mouseleave="handleMouseLeave">
            <MarkdownPreview ref="previewRef" :model-value="content" @html-changed="refresh" />

            <Button
                v-if="hoveredBlock"
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

        <aside
            class="w-72 gap-2 rounded-lg border-surface-200 p-4 dark:border-surface-700 flex shrink-0 flex-col border"
        >
            <h2 class="text-sm font-medium text-surface-900 dark:text-surface-0">Annotations</h2>
            <p v-if="isPending" class="text-sm text-surface-400 italic">Loading annotations…</p>
            <p v-else-if="isError" class="text-sm text-red-500">Failed to load annotations.</p>
            <p v-else class="text-sm text-surface-500">{{ anchors.length }} on this document</p>
        </aside>
    </div>

    <Popover ref="popover">
        <div class="w-80 gap-3 flex flex-col">
            <div v-if="activeAnnotations.length" class="gap-3 max-h-60 flex flex-col overflow-y-auto">
                <div v-for="annotation in activeAnnotations" :key="annotation.id" class="gap-1 flex flex-col">
                    <span class="text-xs font-medium text-surface-500">{{ annotation.author.name }}</span>
                    <p class="text-sm text-surface-900 dark:text-surface-0 whitespace-pre-line">
                        {{ annotation.content }}
                    </p>
                    <div v-if="isOwn(annotation)" class="gap-2 flex">
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
</style>
