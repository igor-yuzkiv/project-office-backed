<script setup lang="ts">
import { computed, onBeforeUnmount, ref, shallowRef, watch } from 'vue'
import { onBeforeRouteLeave } from 'vue-router'
import { useEventListener } from '@vueuse/core'
import { useQueryClient } from '@tanstack/vue-query'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import SelectButton from 'primevue/selectbutton'
import type { IAnnotation } from '@/entities/annotation'
import {
    ProjectDocumentQueryKey,
    ProjectDocumentVersionQueryKey,
    useProjectDocumentVersionAnnotationsQuery,
    useProjectDocumentVersionsQuery,
} from '@/entities/project-document'
import type {
    IProjectDocument,
    IProjectDocumentVersion,
    IUpdateProjectDocumentVersionInput,
} from '@/entities/project-document/types'
import { DocumentCanvas } from '@/shared/components/document-canvas'
import { DocumentSheet } from '@/shared/components/document-sheet'
import { AnnotationComposer, AnnotationPanel, useAnnotationSession } from '@/widgets/project-documents/annotations'
import { DocumentContentEditor, useDocumentContentAutosave } from '@/widgets/project-documents/content-editor'
import {
    DocumentVersionCreateDialog,
    DocumentVersionIndicator,
    DocumentVersionPanel,
    DocumentVersionUpdateDialog,
    useDocumentVersionActions,
    useOpenDocumentVersion,
} from '@/widgets/project-documents/versions'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { SideTab, SideTabs } from '@/shared/components/side-tabs'
import { useConfirmDialog, useTabbedSidePanel, useToast } from '@/shared/composables'
import { ApiError } from '@/shared/api'
import type { LaravelValidationErrors } from '@/shared/types'
import { EMPTY_DOM_BLOCKS, type DomBlocks } from '@/shared/utils/markdown-anchor.dom.util'
import { formatDate } from '@/shared/utils/date.util'

type Mode = 'view' | 'edit' | 'annotate'

const props = defineProps<{
    document: IProjectDocument
}>()

const queryClient = useQueryClient()
const authStore = useAuthStore()
const toast = useToast()
const confirm = useConfirmDialog()

const { versions, openVersionId, openVersion, selectVersion } = useOpenDocumentVersion(() => props.document.id)
const versionActions = useDocumentVersionActions(() => props.document.id)
const { isPending: isVersionsPending } = useProjectDocumentVersionsQuery(() => props.document.id)

// Local to the page: reading is the default, and a document opened fresh is opened to read.
const mode = ref<Mode>('view')
const MODE_OPTIONS: Array<{ label: string; value: Mode }> = [
    { label: 'View', value: 'view' },
    { label: 'Edit', value: 'edit' },
    { label: 'Annotate', value: 'annotate' },
]

const isEditing = computed(() => mode.value === 'edit')

const autosave = useDocumentContentAutosave({
    documentId: () => props.document.id,
    versionId: () => openVersion.value?.id ?? null,
    serverContent: () => openVersion.value?.content ?? null,
})

const saveStatusLabel = computed(() => {
    switch (autosave.status.value) {
        case 'saving':
            return 'Saving…'
        case 'error':
            return 'Not saved'
        case 'saved':
            return `Saved ${formatDate(autosave.lastSavedAt.value, 'HH:mm')}`
        default:
            return ''
    }
})

// The sheet finds the blocks in its own DOM and hands them over; the session resolves
// annotations against them. shallowRef, because these hold live elements.
const blocks = shallowRef<DomBlocks>(EMPTY_DOM_BLOCKS)

const {
    selectedBlock,
    draft,
    editing,
    reanchoring,
    orderedAnchors,
    isPending,
    isError,
    isBusy,
    isReanchoring,
    isComposing,
    refetch,
    pickBlock,
    save,
    cancel,
    selectAnnotation,
    editAnnotation,
    startReanchoring,
    cancelReanchoring,
    removeAnnotation,
} = useAnnotationSession(
    () => openVersion.value?.id ?? '',
    () => blocks.value,
    // No rendered text while editing, so there is nothing to anchor to. A document with no
    // versions renders no sheet either.
    { enabled: () => !isEditing.value && openVersion.value !== null }
)

// Editing shows the annotations as a plain list, straight from the query: the session would
// resolve them against a DOM that is not there and call every one of them lost.
const flatAnnotations = useProjectDocumentVersionAnnotationsQuery(() => openVersion.value?.id ?? '', {
    enabled: () => isEditing.value && openVersion.value !== null,
})

const currentUserId = computed(() => authStore.user?.id ?? null)

const sidebar = useTabbedSidePanel('docs:sidebar')

const annotationPanelProps = computed(() =>
    isEditing.value
        ? {
              anchors: flatAnnotations.annotations.value.map((annotation) => ({
                  annotation,
                  block: null,
                  kind: null,
              })),
              isPending: flatAnnotations.isPending.value,
              isError: flatAnnotations.isError.value,
              editingId: null,
              reanchoringId: null,
              currentUserId: currentUserId.value,
              readonly: true,
          }
        : {
              anchors: orderedAnchors.value,
              isPending: isPending.value,
              isError: isError.value,
              editingId: editing.value?.id ?? null,
              reanchoringId: reanchoring.value?.id ?? null,
              currentUserId: currentUserId.value,
              readonly: mode.value !== 'annotate',
          }
)

const annotationPanelHandlers = computed(() =>
    isEditing.value
        ? { retry: () => flatAnnotations.refetch() }
        : {
              select: selectAnnotation,
              edit: editAnnotation,
              delete: (annotation: IAnnotation) => removeAnnotation(annotation.id),
              reanchor: startReanchoring,
              retry: () => refetch(),
          }
)

// --- Versions ------------------------------------------------------------------------------

const showVersionCreateDialog = ref(false)
// A document with no versions is created into straight away: the dialog was opened to start
// writing, not to look at an empty sheet.
let editAfterCreate = false

function reportError(error: unknown, fallback: string) {
    toast.error(error instanceof ApiError ? error.displayMessage : fallback)
}

function openCreateDialog(thenEdit = false) {
    editAfterCreate = thenEdit
    showVersionCreateDialog.value = true
}

async function createVersion(input: { label: string | null; copyContentFromVersionId: string | null }) {
    // Copying reads the source on the server, so whatever is still in the buffer goes first.
    await autosave.flush()

    try {
        const created = await versionActions.create({
            label: input.label,
            copy_content_from_version_id: input.copyContentFromVersionId,
        })
        showVersionCreateDialog.value = false
        selectVersion(created)
        if (editAfterCreate) mode.value = 'edit'
    } catch (error) {
        reportError(error, 'The version could not be created.')
    }
}

const editingVersion = ref<IProjectDocumentVersion | null>(null)
const versionValidationErrors = ref<LaravelValidationErrors>({})

function openUpdateDialog(version: IProjectDocumentVersion) {
    versionValidationErrors.value = {}
    editingVersion.value = version
}

async function updateVersion(input: IUpdateProjectDocumentVersionInput) {
    const version = editingVersion.value

    if (!version) return

    versionValidationErrors.value = {}

    try {
        await versionActions.update(version, input)
        editingVersion.value = null
    } catch (error) {
        if (error instanceof ApiError && error.isValidationError) versionValidationErrors.value = error.validationErrors ?? {}
        else reportError(error, 'The version could not be saved.')
    }
}

async function removeVersion(version: IProjectDocumentVersion) {
    const confirmed = await confirm.requireAsync({
        header: 'Delete version',
        message: `Version ${version.version_number} will be deleted permanently, together with anything unsaved in it. This cannot be undone.`,
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
    })

    if (!confirmed) return

    // A draft for the version being deleted has nowhere to go, and flushing it would write to
    // an id that is about to be gone.
    if (version.id === openVersion.value?.id) autosave.cancel()

    try {
        await versionActions.remove(version)
    } catch (error) {
        reportError(error, 'The version could not be deleted.')
    }
}

async function setPrimary(versionId: string | null) {
    try {
        await versionActions.setPrimary(versionId)
    } catch (error) {
        reportError(error, 'The primary version could not be changed.')
    }
}

// The open version is chosen by useOpenDocumentVersion once the list settles; when nothing is
// left there is nothing to edit either.
watch(openVersion, (version) => {
    if (version === null) mode.value = 'view'
})

// --- Leaving the editor -------------------------------------------------------------------

const canvasRef = ref<InstanceType<typeof DocumentCanvas>>()

// Autosave patches the cache version by version; leaving the editor is the one moment the
// whole list and the document detail are refreshed for real.
function invalidateVersions() {
    queryClient.invalidateQueries({ queryKey: ProjectDocumentVersionQueryKey.documentVersions(props.document.id) })
    queryClient.invalidateQueries({ queryKey: ProjectDocumentQueryKey.all })
}

// Swapping the sheet for the editor remounts the canvas content, and the reader's place in the
// text would go with it. The editor scrolls inside itself and leaves the canvas at the top, so the
// offset is taken only when leaving a reading mode and put back when one is entered again. A
// failed save does not stop the switch: the draft stays in the buffer, marked Not saved.
let readerScrollTop = 0
let restoreScrollOnRender = false

watch(mode, async (_next, previous) => {
    if (previous !== 'edit') {
        readerScrollTop = canvasRef.value?.scrollElement?.scrollTop ?? 0

        return
    }

    // The sheet mounts empty and fills once the markdown is rendered; an offset set before that
    // is clamped to zero, so the restore waits for the first blocks to arrive. Flagged before
    // anything is awaited: the sheet mounts in this same flush.
    restoreScrollOnRender = true

    await autosave.flush()
    invalidateVersions()
})

function handleBlocksChanged(next: DomBlocks) {
    blocks.value = next

    if (!restoreScrollOnRender || next.blocks.length === 0) return

    restoreScrollOnRender = false
    if (canvasRef.value?.scrollElement) canvasRef.value.scrollElement.scrollTop = readerScrollTop
}

watch(
    () => props.document.id,
    () => {
        void autosave.flush()
        mode.value = 'view'
    }
)

// The buffer dies with the page, so a failed save here keeps the reader on it. This covers the
// page's own tabs too — they are routes, and the tab component goes with them.
onBeforeRouteLeave(async () => {
    const saved = await autosave.flush()

    if (saved && mode.value === 'edit') invalidateVersions()

    return saved
})

// The browser's own prompt: it cannot report a failed save, but it stops the tab closing on an
// unsaved draft.
useEventListener(window, 'beforeunload', (event) => {
    if (!autosave.isDirty.value) return

    event.preventDefault()
})

onBeforeUnmount(() => {
    void autosave.flush()
})
</script>

<template>
    <div v-if="openVersion" class="min-h-0 flex flex-1 overflow-hidden">
        <!-- min-w-0: without it this column is as wide as its widest child, and one unbreakable
             code block in the document would push the sidebar off the screen. -->
        <div class="min-h-0 min-w-0 flex flex-1 flex-col">
            <DocumentCanvas ref="canvasRef">
                <template #start>
                    <div class="gap-2 flex flex-wrap items-center">
                        <DocumentVersionIndicator :version="openVersion" />

                        <!-- Wrapping rather than clipping: the column is narrow whenever the tree and a
                             side panel are both open, and the toolbar has to survive that. -->
                        <div class="gap-2 ml-auto flex flex-wrap items-center justify-end">
                            <template v-if="isEditing">
                                <span
                                    class="text-xs"
                                    :class="autosave.status.value === 'error' ? 'text-red-500' : 'text-surface-500'"
                                >
                                    {{ saveStatusLabel }}
                                </span>
                                <Button
                                    label="Save"
                                    size="small"
                                    severity="secondary"
                                    outlined
                                    :loading="autosave.status.value === 'saving'"
                                    @click="autosave.flush()"
                                />
                            </template>

                            <SelectButton
                                v-model="mode"
                                :options="MODE_OPTIONS"
                                option-label="label"
                                option-value="value"
                                :allow-empty="false"
                                size="small"
                                aria-label="Mode"
                            />
                        </div>
                    </div>

                    <div
                        v-if="mode === 'annotate' && isReanchoring"
                        class="gap-3 rounded-lg p-3 bg-primary-50 dark:bg-primary-950 flex items-center justify-between"
                    >
                        <span class="text-sm text-surface-700 dark:text-surface-200">
                            Select the block this annotation belongs to.
                        </span>
                        <Button label="Cancel" severity="secondary" size="small" @click="cancelReanchoring" />
                    </div>
                </template>

                <DocumentContentEditor
                    v-if="isEditing"
                    v-model="autosave.value.value"
                    :document-id="document.id"
                    @save="autosave.flush()"
                />

                <DocumentSheet
                    v-else
                    :selected-block="selectedBlock"
                    :content="autosave.value.value"
                    :blocks-pickable="mode === 'annotate'"
                    show-catalog
                    @blocks-changed="handleBlocksChanged"
                    @pick-block="pickBlock"
                />
            </DocumentCanvas>

            <!-- Docked like a chat composer: it appears once a block is picked, and never covers the text. -->
            <AnnotationComposer
                v-if="mode === 'annotate' && isComposing"
                :key="selectedBlock?.descriptor.index"
                v-model:draft="draft"
                :is-editing="editing !== null"
                :is-saving="isBusy"
                @save="save"
                @cancel="cancel"
            />
        </div>

        <SideTabs :panel="sidebar" side="right" width="24rem">
            <SideTab value="versions" icon="heroicons:clock" label="Versions">
                <DocumentVersionPanel
                    :versions="versions"
                    :open-version-id="openVersionId"
                    :has-pinned-version="document.primary_version_id !== null"
                    :is-busy="versionActions.isBusy.value"
                    :is-pending="isVersionsPending"
                    @open="selectVersion"
                    @create="openCreateDialog()"
                    @edit="openUpdateDialog"
                    @delete="removeVersion"
                    @set-primary="setPrimary($event.id)"
                    @use-latest-as-primary="setPrimary(null)"
                />
            </SideTab>

            <SideTab value="annotations" icon="heroicons:chat-bubble-left" label="Annotations">
                <AnnotationPanel v-bind="annotationPanelProps" v-on="annotationPanelHandlers" />
            </SideTab>
        </SideTabs>
    </div>

    <div v-else class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center">
        <Icon icon="heroicons:document" class="text-surface-300 text-4xl" />
        <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">This document has no versions yet</p>
        <p class="text-surface-500 max-w-sm text-xs">
            It can stay a section that only holds nested documents, or you can write a first version of it.
        </p>
        <Button label="Create a version" size="small" outlined @click="openCreateDialog(true)" />
    </div>

    <DocumentVersionUpdateDialog
        :visible="editingVersion !== null"
        :version="editingVersion"
        :validation-errors="versionValidationErrors"
        :is-pending="versionActions.isBusy.value"
        @update:visible="(open: boolean) => !open && (editingVersion = null)"
        @submit="updateVersion"
    />

    <DocumentVersionCreateDialog
        v-model:visible="showVersionCreateDialog"
        :versions="versions"
        :is-pending="versionActions.isBusy.value"
        @submit="createVersion"
    />
</template>
