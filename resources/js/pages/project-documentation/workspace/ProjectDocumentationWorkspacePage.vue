<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { useProjectQuery } from '@/entities/project/queries'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'
import { useProjectDocumentActions } from '@/widgets/project-documents/document-actions'
import { ProjectDocumentMoveDialog } from '@/widgets/project-documents/move-dialog'
import { DocumentDetailsPanel } from '@/widgets/project-documents/document-details'
import { DocumentationTreePanel, useDocumentationTree } from '@/widgets/project-documents/documentation-tree'
import { useBreadcrumbs, useHeaderActions } from '@/app/shell'
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
        if (deletedId === documentId.value) openDocumentationRoot()
    },
})

const { editRoute, annotationRoute, moveDialog, remove } = useProjectDocumentActions(openedDocument, {
    onMoved: () => tree.reload(),
    onDeleted: (document) => {
        tree.forgetLevel(document.id)
        openDocumentationRoot()
        tree.reload()
    },
})

useHeaderActions(() =>
    openedDocument.value
        ? [
              { key: 'edit-project-document', title: 'Edit', to: editRoute.value, is_primary: true },
              ...(annotationRoute.value
                  ? [{ key: 'annotate-project-document', title: 'Annotation mode', to: annotationRoute.value }]
                  : []),
              { key: 'move-project-document', title: 'Move', action: moveDialog.open },
              { key: 'delete-project-document', title: 'Delete', action: remove },
          ]
        : []
)

// The document's path ends with the document itself; everything before it is the
// branch the tree has to open to reveal it.
const ancestorIds = computed(() => (openedDocument.value?.path ?? []).slice(0, -1).map((node) => node.id))

function openDocument(id: string) {
    router.push({ name: 'project-documentation.document', params: { projectId: projectId.value, documentId: id } })
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
            <div class="border-surface-200 dark:border-surface-700 w-92 shrink-0 overflow-hidden border-r">
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

            <section class="min-w-0 flex flex-1 flex-col overflow-auto">
                <RouterView v-slot="{ Component }">
                    <component :is="Component" @create-document="tree.createRootDocument" />
                </RouterView>
            </section>

            <div
                class="border-surface-200 dark:border-surface-700 shrink-0 overflow-hidden border-l"
                :class="isDetailsPanelOpen ? 'w-80' : 'w-11'"
            >
                <DocumentDetailsPanel v-model:open="isDetailsPanelOpen" />
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
