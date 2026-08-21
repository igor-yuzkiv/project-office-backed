<script setup lang="ts">
import { computed, useTemplateRef, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import SplitButton from 'primevue/splitbutton'
import type { MenuItem } from 'primevue/menuitem'
import { useProjectQuery } from '@/entities/project/queries'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'
import { useProjectDocumentActions } from '@/widgets/project-documents/document-actions'
import { ProjectDocumentMoveDialog } from '@/widgets/project-documents/move-dialog'
import { DocumentationTreePanel, useDocumentationTree } from '@/widgets/project-documents/documentation-tree'
import { useBreadcrumbs } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

// What the edit page exposes for the toolbar to drive. Vue unwraps exposed refs, so
// these arrive as plain values, not refs.
interface EditPane {
    save: () => void
    cancel: () => void
    abandon: () => void
    isDirty: boolean
    isSaving: boolean
}

const router = useRouter()
const layoutStore = useAppLayoutStore()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId', '')

const { project } = useProjectQuery(projectId)

// Shares the cache entry the pages fill; read here only for the toolbar and to know
// which branch of the tree to reveal.
const { projectDocument } = useProjectDocumentQuery(
    documentId,
    { with_path: true },
    { enabled: () => Boolean(documentId.value) }
)

const pane = useTemplateRef<EditPane>('pane')

const openedDocument = computed(() =>
    projectDocument.value?.project_id === projectId.value ? projectDocument.value : undefined
)

const isEditing = computed(() => router.currentRoute.value.name === 'project-documentation.document.edit')

const tree = useDocumentationTree(projectId, {
    onCreated: (document) => openDocument(document.id),
    onDeleted: (deletedId) => {
        if (deletedId !== documentId.value) return

        // Its draft has nowhere to go back to, so leaving must not ask about it.
        pane.value?.abandon?.()
        openDocumentationRoot()
    },
})

const { annotationRoute, moveDialog, remove } = useProjectDocumentActions(openedDocument, {
    onMoved: () => tree.reload(),
    onDeleted: (document) => {
        tree.forgetLevel(document.id)
        openDocumentationRoot()
        tree.reload()
    },
})

const documentMenuItems = computed<MenuItem[]>(() => [
    { label: 'Move', icon: 'pi pi-arrows-h', command: () => moveDialog.open() },
    { label: 'Delete', icon: 'pi pi-trash', command: () => remove() },
])

const ancestorIds = computed(() => (openedDocument.value?.path ?? []).slice(0, -1).map((node) => node.id))

// The collapsed details panel is a preference, not a property of the document being
// read, so it rides along; the body tab does not, because another document opens on
// its own first tab.
const panelQuery = computed(() =>
    router.currentRoute.value.query.details ? { details: router.currentRoute.value.query.details } : {}
)

function openDocument(id: string) {
    router.push({
        name: 'project-documentation.document',
        params: { projectId: projectId.value, documentId: id },
        query: panelQuery.value,
    })
}

function openEditor() {
    router.push({
        name: 'project-documentation.document.edit',
        params: { projectId: projectId.value, documentId: documentId.value },
        query: router.currentRoute.value.query,
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

            <div class="min-w-0 flex flex-1 flex-col overflow-hidden">
                <div class="gap-2 px-3 py-1.5 flex shrink-0 items-center" style="min-height: 2.75rem">
                    <!-- The saved name, kept visible while the editable heading below shows
                         the draft: it is what the draft is being compared against. -->
                    <div v-if="openedDocument" class="gap-2 min-w-0 flex items-baseline">
                        <span class="text-surface-400 text-xs shrink-0">{{ openedDocument.key }}</span>
                        <span class="text-surface-600 dark:text-surface-300 text-sm truncate">
                            {{ openedDocument.title }}
                        </span>
                    </div>

                    <div class="gap-1 ml-auto flex shrink-0 items-center">
                        <template v-if="isEditing">
                            <span v-if="pane?.isDirty" class="mr-2 text-xs text-amber-600 dark:text-amber-400">
                                Unsaved changes
                            </span>
                            <Button label="Cancel" size="small" text severity="secondary" @click="pane?.cancel()">
                                <template #icon><Icon icon="heroicons:x-mark" class="mr-1 text-base" /></template>
                            </Button>
                            <Button
                                :label="pane?.isSaving ? 'Saving…' : 'Save'"
                                size="small"
                                text
                                :disabled="pane?.isSaving"
                                @click="pane?.save()"
                            >
                                <template #icon><Icon icon="heroicons:check" class="mr-1 text-base" /></template>
                            </Button>
                        </template>

                        <template v-else-if="openedDocument">
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

                            <SplitButton
                                label="Edit"
                                size="small"
                                text
                                severity="secondary"
                                :model="documentMenuItems"
                                @click="openEditor"
                            />
                        </template>

                        <Button
                            v-if="!isEditing"
                            label="Add"
                            size="small"
                            text
                            severity="secondary"
                            @click="tree.createRootDocument"
                        >
                            <template #icon><Icon icon="material-symbols:add" class="mr-1 text-base" /></template>
                        </Button>
                    </div>
                </div>

                <RouterView v-slot="{ Component }">
                    <!-- Keyed by document: a jump straight from one document's editor to
                         another must not carry the first one's draft into the second. -->
                    <component
                        :is="Component"
                        :key="documentId"
                        ref="pane"
                        @create-document="tree.createRootDocument"
                        @saved="tree.reload()"
                    />
                </RouterView>
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
