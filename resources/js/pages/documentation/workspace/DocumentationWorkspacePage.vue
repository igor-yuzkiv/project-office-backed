<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import Skeleton from 'primevue/skeleton'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useProjectQuery } from '@/entities/project/queries'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'
import { ProjectDocumentMoveDialog, useProjectDocumentMove } from '@/widgets/project-documents/move-dialog'
import { DocumentationTreePanel, useDocumentationTree } from '@/widgets/project-documents/documentation-tree'
import { useBreadcrumbs } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useLocalStorage } from '@vueuse/core'

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

const belongsElsewhere = computed(() => !!projectDocument.value && projectDocument.value.project_id !== projectId.value)

const openedDocument = computed(() => (belongsElsewhere.value ? undefined : projectDocument.value))

const tree = useDocumentationTree(projectId, {
    onCreated: (document) => openDocument(document.id),
    onDeleted: (deletedId) => {
        if (deletedId === documentId.value) openDocumentationRoot()
    },
})

// Hiding the column is a preference worth keeping; opening the drawer over the document is a
// moment, and leaving it takes nothing back.
const isTreeCollapsed = useLocalStorage('docs:tree-collapsed', false)
const isTreeDrawerOpen = ref(false)

// The same panel is rendered in two places — a column and a drawer — and its bindings are
// described once so the two cannot drift apart.
const treePanelProps = computed(() => ({
    rows: tree.rows.value,
    isPending: tree.isPending.value,
    isError: tree.isError.value,
    selectedDocumentId: documentId.value || null,
}))

const treePanelHandlers = {
    collapse: collapseTree,
    select: openDocument,
    'toggle-node': tree.toggleNode,
    'load-more': tree.loadMore,
    'expand-all': tree.expandAll,
    'create-root': tree.createRootDocument,
    'create-child': tree.createChildDocument,
    delete: tree.deleteDocument,
    retry: tree.load,
}

function collapseTree() {
    isTreeCollapsed.value = true
}

function dockTree() {
    isTreeCollapsed.value = false
    isTreeDrawerOpen.value = false
}

const moveDialog = useProjectDocumentMove(() => documentId.value, { onMoved: () => tree.reload() })

function removeDocument() {
    const document = openedDocument.value

    if (document) tree.deleteDocument(document)
}

const tabs = computed(() => [
    { value: 'details', label: 'Details', route: 'project-documentation.document.details', count: undefined },
    { value: 'document', label: 'Document', route: 'project-documentation.document', count: undefined },
    {
        value: 'comments',
        label: 'Comments',
        route: 'project-documentation.document.comments',
        count: openedDocument.value?.comments_count,
    },
    {
        value: 'tasks',
        label: 'Related tasks',
        route: 'project-documentation.document.tasks',
        count: openedDocument.value?.tasks_count,
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
    isTreeDrawerOpen.value = false

    router.push({
        name: 'project-documentation.document',
        params: { projectId: projectId.value, documentId: id },
    })
}

function openEditor() {
    router.push({
        name: 'project-documentation.document.edit',
        params: { projectId: projectId.value, documentId: documentId.value },
    })
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
    <div class="gap-2 p-2 flex flex-1 overflow-hidden">
        <div
            class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 rounded-xl flex flex-1 overflow-hidden border"
        >
            <div
                v-if="!isTreeCollapsed"
                class="border-surface-200 dark:border-surface-700 w-100 shrink-0 overflow-hidden border-r"
            >
                <DocumentationTreePanel v-bind="treePanelProps" v-on="treePanelHandlers" />
            </div>

            <!-- What is left of the column: a full-height strip holding the same control in the
                 same place. Hovering it peeks at the tree, clicking brings the column back. -->
            <div
                v-else
                class="border-surface-200 dark:border-surface-700 py-1.5 w-11 flex shrink-0 flex-col items-center border-r"
                @mouseenter="isTreeDrawerOpen = true"
            >
                <Button
                    severity="secondary"
                    text
                    rounded
                    size="small"
                    aria-label="Show the document list"
                    title="Show the document list"
                    @click="dockTree"
                >
                    <template #icon>
                        <Icon icon="heroicons:bars-3" class="text-base" />
                    </template>
                </Button>
            </div>

            <div class="min-w-0 flex flex-1 flex-col overflow-hidden">
                <RouterView v-if="!documentId" @create-document="tree.createRootDocument" />

                <div v-else-if="belongsElsewhere" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
                    <Icon icon="heroicons:document-magnifying-glass" class="text-surface-300 text-4xl" />
                    <p class="text-surface-700 dark:text-surface-200 text-base font-medium">Document not found</p>
                    <p class="text-surface-500 max-w-sm text-sm text-center">
                        It was deleted or moved to another project. Pick another document on the left, or go back to the
                        documentation root.
                    </p>
                    <Button
                        label="Back to documentation root"
                        size="small"
                        severity="secondary"
                        @click="openDocumentationRoot()"
                    />
                </div>

                <div v-else-if="isError" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
                    <Icon icon="heroicons:exclamation-triangle" class="text-2xl text-red-500" />
                    <p class="text-surface-700 dark:text-surface-200 text-base font-medium">
                        Could not load the document
                    </p>
                    <p class="text-surface-500 max-w-sm text-sm text-center">
                        Check your connection and try again. The document tree stays available.
                    </p>
                    <Button label="Try again" size="small" severity="secondary" @click="refetch()" />
                </div>

                <div v-else-if="!openedDocument && isFetching" class="gap-3 p-8 flex flex-col">
                    <Skeleton height="2rem" width="20rem" />
                    <Skeleton v-for="n in 6" :key="n" height="1rem" />
                </div>

                <template v-else-if="openedDocument">
                    <div class="gap-2 px-4 pt-2 flex items-center" style="min-height: 2.75rem">
                        <div class="gap-2 min-w-0 flex items-baseline">
                            <span class="text-surface-400 text-sm shrink-0">{{ openedDocument.key }}</span>
                            <h1 class="text-surface-900 dark:text-surface-0 text-xl font-semibold truncate">
                                {{ openedDocument.title }}
                            </h1>
                        </div>

                        <div class="gap-1 ml-auto flex shrink-0 items-center">
                            <Button label="Edit" size="small" text severity="secondary" @click="openEditor">
                                <template #icon><Icon icon="heroicons:pencil" class="mr-1 text-base" /></template>
                            </Button>

                            <Button label="Move" size="small" text severity="secondary" @click="moveDialog.open()">
                                <template #icon
                                    ><Icon icon="heroicons:arrows-right-left" class="mr-1 text-base"
                                /></template>
                            </Button>

                            <Button label="Delete" size="small" text severity="secondary" @click="removeDocument()">
                                <template #icon><Icon icon="heroicons:trash" class="mr-1 text-base" /></template>
                            </Button>
                        </div>
                    </div>

                    <Tabs :value="activeTab" @update:value="openTab(String($event))">
                        <TabList>
                            <Tab v-for="tab in tabs" :key="tab.value" :value="tab.value" class="px-4 py-2">
                                {{ tab.label }}
                                <span v-if="tab.count" class="text-surface-400 ml-1 text-xs">{{ tab.count }}</span>
                            </Tab>
                        </TabList>
                    </Tabs>

                    <!-- Each tab scrolls itself: the annotation sheet keeps its own canvas and its
                         sidebar has to reach full height. -->
                    <div class="min-h-0 flex flex-1 flex-col overflow-hidden">
                        <RouterView v-slot="{ Component }">
                            <component :is="Component" :document="openedDocument" />
                        </RouterView>
                    </div>
                </template>
            </div>
        </div>

        <!-- Only while the column is hidden, so the panel is never mounted twice. A peek rather
             than a mode: no mask over the document, and it leaves when the pointer does. The
             panel's own header button docks it here instead of hiding what is already hidden. -->
        <Drawer
            v-if="isTreeCollapsed"
            v-model:visible="isTreeDrawerOpen"
            position="left"
            :modal="false"
            class="!w-100 !max-w-full"
            :pt="{ header: { class: 'hidden' }, content: { class: '!p-0' } }"
        >
            <div class="h-full" @mouseleave="isTreeDrawerOpen = false">
                <DocumentationTreePanel v-bind="treePanelProps" v-on="{ ...treePanelHandlers, collapse: dockTree }" />
            </div>
        </Drawer>

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
