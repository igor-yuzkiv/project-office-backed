<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useEventListener } from '@vueuse/core'
import { onBeforeRouteLeave, onBeforeRouteUpdate, useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import { useProjectQuery } from '@/entities/project/queries'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'
import { useProjectDocumentActions } from '@/widgets/project-documents/document-actions'
import { useProjectDocumentEditing } from '@/widgets/project-documents/document-editing'
import { ProjectDocumentMoveDialog } from '@/widgets/project-documents/move-dialog'
import { DocumentDetailsPanel } from '@/widgets/project-documents/document-details'
import { DocumentationTreePanel, useDocumentationTree } from '@/widgets/project-documents/documentation-tree'
import { useBreadcrumbs } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

const router = useRouter()
const layoutStore = useAppLayoutStore()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId', '')

const { project } = useProjectQuery(projectId)

// Shares the cache entry the opened document's pane fills, and is read here only to
// know which branch of the tree to reveal.
const { projectDocument } = useProjectDocumentQuery(
    documentId,
    { with_path: true },
    { enabled: () => Boolean(documentId.value) }
)

const isDetailsPanelOpen = ref(true)

// The document the workspace has open, as opposed to one that failed to load or
// belongs elsewhere: those are not this project's document and get no actions.
const openedDocument = computed(() =>
    projectDocument.value?.project_id === projectId.value ? projectDocument.value : undefined
)

// Owned here rather than in the tree panel: the panel is remounted whenever the
// workspace layout changes, and a remount must not throw away loaded levels.
const tree = useDocumentationTree(projectId, {
    onCreated: (document) => openDocument(document.id),
    onDeleted: (deletedId) => {
        if (deletedId !== documentId.value) return

        // Its draft has nowhere to go back to, so leaving must not ask about it.
        editing.cancel()
        openDocumentationRoot()
    },
})

const editing = useProjectDocumentEditing(openedDocument, { onSaved: () => tree.reload() })

const { annotationRoute, moveDialog, remove } = useProjectDocumentActions(openedDocument, {
    onMoved: () => tree.reload(),
    onDeleted: (document) => {
        editing.cancel()
        tree.forgetLevel(document.id)
        openDocumentationRoot()
        tree.reload()
    },
})

async function discardEditing() {
    if (await editing.confirmDiscard()) editing.cancel()
}

// Both guards are needed: moving between documents reuses this component, so it is
// an update rather than a leave.
onBeforeRouteUpdate(() => editing.confirmDiscard())
onBeforeRouteLeave(() => editing.confirmDiscard())

useEventListener(window, 'beforeunload', (event: BeforeUnloadEvent) => {
    if (!editing.isDirty.value) return

    event.preventDefault()
    event.returnValue = ''
})

// The document's path ends with the document itself; everything before it is the
// branch the tree has to open to reveal it.
const ancestorIds = computed(() => (openedDocument.value?.path ?? []).slice(0, -1).map((node) => node.id))

function openDocument(id: string) {
    router.push({ name: 'project-documentation.document', params: { projectId: projectId.value, documentId: id } })
}

function openRelatedTasksTab() {
    router.replace({
        name: 'project-documentation.document',
        params: { projectId: projectId.value, documentId: documentId.value },
        query: { tab: 'tasks' },
    })
}

function openAnnotationMode() {
    if (annotationRoute.value) router.push(annotationRoute.value)
}

function openDocumentationRoot() {
    router.push({ name: 'project-documentation', params: { projectId: projectId.value } })
}

useBreadcrumbs(() => [
    { label: 'Projects', to: { name: 'projects' } },
    { label: project.value?.name ?? 'Project', to: { name: 'project-details', params: { id: projectId.value } } },
    { label: 'Documentation' },
])

watch(projectId, () => tree.load(), { immediate: true })

watch(
    ancestorIds,
    (ids) => {
        if (ids.length) tree.expandAncestors(ids)
    },
    { immediate: true }
)

watch(
    project,
    (value) => {
        if (value) layoutStore.setPageTitle(`Documentation | ${value.name}`)
    },
    { immediate: true }
)
</script>

<template>
    <div class="gap-2 p-2 flex flex-1 overflow-hidden">
        <div
            class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 rounded-xl flex flex-1 overflow-hidden border"
        >
            <div class="border-surface-200 dark:border-surface-700 w-100 shrink-0 overflow-hidden border-r">
                <DocumentationTreePanel
                    :rows="tree.rows.value"
                    :is-pending="tree.isPending.value"
                    :is-error="tree.isError.value"
                    :selected-document-id="documentId || null"
                    @select="openDocument"
                    @toggle-node="tree.toggleNode"
                    @load-more="tree.loadMore"
                    @create-root="tree.createRootDocument"
                    @create-child="tree.createChildDocument"
                    @delete="tree.deleteNodeDocument"
                    @retry="tree.load"
                />
            </div>

            <section class="min-w-0 flex flex-1 flex-col overflow-hidden">
                <div
                    v-if="openedDocument"
                    class="border-surface-200 dark:border-surface-700 gap-1 px-3 py-1.5 flex shrink-0 items-center justify-end border-b"
                >
                    <template v-if="editing.isEditing.value">
                        <span v-if="editing.isDirty.value" class="mr-2 text-xs text-amber-600 dark:text-amber-400">
                            Unsaved changes
                        </span>
                        <Button
                            :label="editing.isSaving.value ? 'Saving…' : 'Save'"
                            size="small"
                            text
                            :disabled="editing.isSaving.value"
                            @click="editing.save"
                        >
                            <template #icon><Icon icon="heroicons:check" class="mr-1 text-base" /></template>
                        </Button>
                        <Button label="Cancel" size="small" text severity="secondary" @click="discardEditing">
                            <template #icon><Icon icon="heroicons:x-mark" class="mr-1 text-base" /></template>
                        </Button>
                    </template>

                    <template v-else>
                        <Button label="Edit" size="small" text severity="secondary" @click="editing.start">
                            <template #icon><Icon icon="heroicons:pencil-square" class="mr-1 text-base" /></template>
                        </Button>
                        <Button
                            v-if="annotationRoute"
                            label="Annotate"
                            size="small"
                            text
                            severity="secondary"
                            @click="openAnnotationMode"
                        >
                            <template #icon>
                                <Icon icon="heroicons:chat-bubble-left-right" class="mr-1 text-base" />
                            </template>
                        </Button>
                        <Button label="Move" size="small" text severity="secondary" @click="moveDialog.open">
                            <template #icon
                                ><Icon icon="heroicons:arrows-right-left" class="mr-1 text-base"
                            /></template>
                        </Button>
                        <Button label="Delete" size="small" text severity="danger" @click="remove">
                            <template #icon><Icon icon="heroicons:trash" class="mr-1 text-base" /></template>
                        </Button>
                    </template>
                </div>

                <RouterView v-slot="{ Component }">
                    <component
                        :is="Component"
                        v-model:draft-title="editing.draft.value.title"
                        v-model:draft-content="editing.draft.value.content"
                        :is-editing="editing.isEditing.value"
                        :handle-image-upload="editing.handleContentImageUpload"
                        @create-document="tree.createRootDocument"
                    />
                </RouterView>
            </section>

            <div
                class="border-surface-200 dark:border-surface-700 shrink-0 overflow-hidden border-l"
                :class="isDetailsPanelOpen ? 'w-96' : 'w-12'"
            >
                <DocumentDetailsPanel
                    v-model:open="isDetailsPanelOpen"
                    v-model:draft-status="editing.draft.value.status"
                    v-model:draft-tags="editing.draft.value.tags"
                    :is-editing="editing.isEditing.value"
                    :document="openedDocument"
                    @open-document="openDocument"
                    @view-all-tasks="openRelatedTasksTab"
                />
            </div>
        </div>

        <ProjectDocumentMoveDialog
            v-if="openedDocument"
            v-model:visible="moveDialog.visible.value"
            :project-id="openedDocument.project_id"
            :current-document-id="openedDocument.id"
            :validation-errors="moveDialog.validationErrors.value"
            @select="moveDialog.handleSelect"
        />

        <ProjectDocumentCreateDialog
            v-model:visible="tree.createDialog.visible.value"
            v-model:form-data="tree.createDialog.formData.value"
            :validation-errors="tree.createDialog.validationErrors.value"
            :is-pending="tree.createDialog.isPending.value"
            :parent-document="tree.createDialog.parentDocument.value"
            @submit="tree.createDialog.submit"
        />
    </div>
</template>
