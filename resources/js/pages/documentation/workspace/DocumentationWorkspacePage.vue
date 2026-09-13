<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import Skeleton from 'primevue/skeleton'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useProjectQuery } from '@/entities/project/queries'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentUpsertDialog } from '@/widgets/project-documents/upsert-dialog'
import { ProjectDocumentMoveDialog, useProjectDocumentMove } from '@/widgets/project-documents/move-dialog'
import { DocumentationTreePanel, useDocumentationTree } from '@/widgets/project-documents/documentation-tree'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { useBreadcrumbs } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { SidePanel } from '@/shared/components/side-panel'
import { IconButton } from '@/shared/components/button'
import { CopyableKey } from '@/shared/components/display'
import { PageHead } from '@/shared/components/page-head'
import { useCollapsibleSidePanel } from '@/shared/composables'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'

const router = useRouter()
const layoutStore = useAppLayoutStore()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId', '')

const { project } = useProjectQuery(projectId)

// The document is loaded once, here: the tab pages receive it and never ask again,
// and the states below are the only place they are handled.
const { projectDocument, isError, isFetching, refetch } = useProjectDocumentQuery(
    documentId,
    { with_path: true },
    { enabled: () => Boolean(documentId.value) }
)

const documentFromAnotherProject = computed(
    () => !!projectDocument.value && projectDocument.value.project_id !== projectId.value
)

const openedDocument = computed(() => (documentFromAnotherProject.value ? undefined : projectDocument.value))

const tree = useDocumentationTree(projectId, {
    onCreated: (document) => openDocument(document.id),
    onDeleted: (deletedId) => {
        if (deletedId === documentId.value) openDocumentationRoot()
    },
})

const treePanel = useCollapsibleSidePanel('docs:tree-collapsed')

// The same panel is rendered in two places — a column and a drawer — and its bindings are
// described once so the two cannot drift apart.
const treePanelProps = computed(() => ({
    rows: tree.rows.value,
    isPending: tree.isPending.value,
    isError: tree.isError.value,
    selectedDocumentId: documentId.value || null,
    projectName: project.value?.name,
}))

const treePanelHandlers = {
    select: openDocument,
    'toggle-node': tree.toggleNode,
    'load-more': tree.loadMore,
    'expand-all': tree.expandAll,
    'create-root': tree.createRootDocument,
    'create-child': tree.createChildDocument,
    delete: tree.deleteDocument,
    retry: tree.load,
}

const moveDialog = useProjectDocumentMove(() => documentId.value, { onMoved: () => tree.reload() })

function removeDocument() {
    const document = openedDocument.value

    if (document) tree.deleteDocument(document)
}

const moreMenu = ref<InstanceType<typeof Menu>>()

const moreMenuItems = computed<MenuItem[]>(() => [
    { label: 'Move', icon: 'pi pi-arrow-right-arrow-left', command: () => moveDialog.open() },
    { separator: true },
    { label: 'Delete', icon: 'pi pi-trash', command: removeDocument },
])

function openMoreMenu(event: MouseEvent) {
    moreMenu.value?.toggle(event)
}

const tabs = computed(() => [
    { value: 'details', label: 'Details', route: 'project-documentation.document.details', count: undefined },
    { value: 'document', label: 'Content', route: 'project-documentation.document', count: undefined },
    {
        value: 'comments',
        label: 'Comments',
        route: 'project-documentation.document.comments',
        count: openedDocument.value?.comments_count,
    },
])

const activeTab = computed(
    () => tabs.value.find((tab) => tab.route === router.currentRoute.value.name)?.value ?? 'document'
)

const ancestorIds = computed(() => (openedDocument.value?.path ?? []).slice(0, -1).map((node) => node.id))

function openTab(value: string) {
    const tab = tabs.value.find((candidate) => candidate.value === value)

    if (!tab) return

    router.push({ name: tab.route, params: { projectId: projectId.value, documentId: documentId.value } })
}

function openDocument(id: string) {
    // The drawer covers what the reader just asked to see, so picking a document dismisses it.
    treePanel.closeDrawer()

    router.push({
        name: 'project-documentation.document',
        params: { projectId: projectId.value, documentId: id },
    })
}

// The same dialog the tree creates documents with; the document's own fields live nowhere else.
function editDocument() {
    if (openedDocument.value) tree.documentDialog.openEdit(openedDocument.value)
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

// The shell drops the override on every route change, and each tab is a route now.
watch(
    [project, () => router.currentRoute.value.name],
    () => {
        if (project.value) layoutStore.setPageTitle(`Documentation | ${project.value.name}`)
    },
    { immediate: true }
)
</script>

<template>
    <div class="flex flex-1 overflow-hidden">
        <SidePanel
            :panel="treePanel"
            side="left"
            width="288px"
            icon="heroicons:bars-3"
            show-label="Show the document list"
        >
            <template #default="{ collapse }">
                <DocumentationTreePanel v-bind="treePanelProps" v-on="{ ...treePanelHandlers, collapse }" />
            </template>
        </SidePanel>

        <div class="bg-page min-w-0 flex flex-1 flex-col overflow-hidden">
            <RouterView v-if="!documentId" @create-document="tree.createRootDocument" />

            <div
                v-else-if="documentFromAnotherProject"
                class="gap-3 p-10 flex flex-1 flex-col items-center justify-center"
            >
                <p class="type-meta max-w-sm text-center">
                    Document not found — it was deleted or moved to another project.
                </p>
                <Button
                    label="Back to documentation root"
                    size="small"
                    severity="secondary"
                    @click="openDocumentationRoot"
                />
            </div>

            <div v-else-if="isError" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
                <p class="type-meta max-w-sm text-center">Could not load the document.</p>
                <Button label="Try again" size="small" severity="secondary" @click="refetch()" />
            </div>

            <div v-else-if="!openedDocument && isFetching" class="gap-3 px-6 pt-5 flex flex-col">
                <Skeleton height="1rem" width="12rem" />
                <Skeleton height="1.75rem" width="24rem" />
                <Skeleton v-for="n in 5" :key="n" height="1rem" />
            </div>

            <template v-else-if="openedDocument">
                <div class="px-6 pt-5">
                    <PageHead :title="openedDocument.title">
                        <template #key>
                            <CopyableKey :value="openedDocument.key" size="md" />
                            <template v-if="openedDocument.version">
                                <span class="text-ink-3 mx-2">·</span>
                                <span>v{{ openedDocument.version.version_number }}</span>
                            </template>
                            <span class="text-ink-3 mx-2">·</span>
                            <ProjectDocumentStatusTag :status="openedDocument.status" />
                        </template>
                        <template v-if="openedDocument.updated_by" #meta>
                            <template v-if="openedDocument.version">
                                v{{ openedDocument.version.version_number }} ·
                            </template>
                            edited {{ formatRelativeTime(openedDocument.updated_at) }} by
                            {{ openedDocument.updated_by.name }}
                        </template>
                        <template #actions>
                            <Button
                                label="Edit"
                                icon="pi pi-pencil"
                                size="small"
                                severity="secondary"
                                outlined
                                @click="editDocument"
                            />
                            <IconButton icon="pepicons-pop:dots-x" aria-label="More" @click="openMoreMenu" />
                        </template>
                    </PageHead>

                    <Tabs :value="activeTab" class="mt-3" @update:value="openTab(String($event))">
                        <TabList>
                            <Tab v-for="tab in tabs" :key="tab.value" :value="tab.value" class="px-2.5 py-2">
                                {{ tab.label }}
                                <span v-if="tab.count !== undefined" class="type-meta-3 ml-1 tabular-nums">
                                    {{ tab.count }}
                                </span>
                            </Tab>
                        </TabList>
                    </Tabs>
                </div>

                <!-- Each tab scrolls itself: the annotation sheet keeps its own canvas and its
                     sidebar has to reach full height. -->
                <div class="min-h-0 flex flex-1 flex-col overflow-hidden">
                    <RouterView v-slot="{ Component }">
                        <component :is="Component" :document="openedDocument" />
                    </RouterView>
                </div>
            </template>
        </div>

        <Menu ref="moreMenu" :model="moreMenuItems" popup />

        <ProjectDocumentMoveDialog
            v-if="openedDocument"
            v-model:visible="moveDialog.visible.value"
            :project-id="openedDocument.project_id"
            :current-document-id="openedDocument.id"
            :validation-errors="moveDialog.validationErrors.value"
            @select="moveDialog.handleSelect"
        />

        <ProjectDocumentUpsertDialog
            v-model:visible="tree.documentDialog.visible.value"
            v-model:form-data="tree.documentDialog.formData.value"
            :mode="tree.documentDialog.mode.value"
            :validation-errors="tree.documentDialog.validationErrors.value"
            :is-pending="tree.documentDialog.isPending.value"
            :parent-document="tree.documentDialog.parentDocument.value"
            @submit="tree.documentDialog.submit"
        />
    </div>
</template>
