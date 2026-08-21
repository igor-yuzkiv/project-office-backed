<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Splitter from 'primevue/splitter'
import SplitterPanel from 'primevue/splitterpanel'
import { useProjectQuery } from '@/entities/project/queries'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'
import { DocumentationTreePanel, useDocumentationTree } from '@/widgets/project-documents/documentation-tree'
import { useBreadcrumbs } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

const router = useRouter()
const layoutStore = useAppLayoutStore()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId', '')

const { project } = useProjectQuery(projectId)
const {
    projectDocument,
    isError: isDocumentError,
    isFetching: isDocumentFetching,
} = useProjectDocumentQuery(documentId, { with_path: true }, { enabled: () => Boolean(documentId.value) })

const isDetailsPanelOpen = ref(true)

// Owned here rather than in the tree panel: collapsing the details panel remounts
// the splitter's subtree, and the loaded tree has to survive that.
const tree = useDocumentationTree(projectId, {
    onCreated: (document) => openDocument(document.id),
    onDeleted: (deletedId) => {
        if (deletedId === documentId.value) openDocumentationRoot()
    },
})

// A document that failed to load, or that belongs to another project, is not this
// project's document — the tree stays usable either way.
const isDocumentMissing = computed(() => {
    if (!documentId.value) return false

    return isDocumentError.value || (!!projectDocument.value && projectDocument.value.project_id !== projectId.value)
})

const openedDocument = computed(() => (isDocumentMissing.value ? undefined : projectDocument.value))

const selectedDocumentId = computed(() => (isDocumentMissing.value ? null : documentId.value || null))

// The document's path ends with the document itself; everything before it is the
// branch the tree has to open to reveal it.
const ancestorIds = computed(() => (openedDocument.value?.path ?? []).slice(0, -1).map((node) => node.id))

function openDocument(id: string) {
    router.push({ name: 'project-documentation', params: { projectId: projectId.value, documentId: id } })
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
    project,
    (value) => {
        if (value) layoutStore.setPageTitle(`Documentation | ${value.name}`)
    },
    { immediate: true }
)
</script>

<template>
    <div class="gap-2 p-2 flex flex-1 overflow-hidden">
        <Splitter
            :key="isDetailsPanelOpen ? 'three-panels' : 'two-panels'"
            class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 rounded-xl flex-1 overflow-hidden border"
        >
            <SplitterPanel :size="22" :min-size="12">
                <DocumentationTreePanel
                    :tree="tree"
                    :selected-document-id="selectedDocumentId"
                    :ancestor-ids="ancestorIds"
                    @select="openDocument"
                />
            </SplitterPanel>

            <SplitterPanel :size="isDetailsPanelOpen ? 56 : 78" :min-size="30">
                <section class="flex h-full flex-col overflow-auto">
                    <div v-if="isDocumentMissing" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
                        <Icon icon="heroicons:document-magnifying-glass" class="text-surface-300 text-4xl" />
                        <p class="text-surface-700 dark:text-surface-200 text-base font-medium">Document not found</p>
                        <p class="text-surface-500 max-w-sm text-sm text-center">
                            It was deleted or moved to another project. Pick another document on the left, or go back to
                            the documentation root.
                        </p>
                        <Button
                            label="Back to documentation root"
                            size="small"
                            severity="secondary"
                            @click="openDocumentationRoot()"
                        />
                    </div>

                    <div v-else-if="!documentId" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
                        <Icon icon="heroicons:document-text" class="text-surface-300 text-4xl" />
                        <p class="text-surface-700 dark:text-surface-200 text-base font-medium">Select a document</p>
                        <p class="text-surface-500 max-w-sm text-sm text-center">
                            The tree shows the documentation structure of this project. Pick a document to open it here.
                        </p>
                    </div>

                    <div v-else-if="!openedDocument && isDocumentFetching" class="gap-3 p-8 flex flex-col">
                        <Skeleton height="2rem" width="20rem" />
                        <Skeleton v-for="n in 6" :key="n" height="1rem" />
                    </div>

                    <div v-else-if="openedDocument" class="gap-2 p-8 flex flex-col">
                        <h1 class="text-surface-900 dark:text-surface-0 text-xl font-semibold">
                            {{ openedDocument.title }}
                        </h1>
                        <span class="text-surface-500 text-xs">{{ openedDocument.key }}</span>
                    </div>
                </section>
            </SplitterPanel>

            <SplitterPanel v-if="isDetailsPanelOpen" :size="22" :min-size="14">
                <section class="flex h-full flex-col overflow-hidden">
                    <header
                        class="border-surface-200 dark:border-surface-700 gap-2 px-3 py-2 flex items-center justify-between border-b"
                    >
                        <h2
                            class="text-surface-600 dark:text-surface-300 text-xs font-semibold tracking-wide uppercase"
                        >
                            Document details
                        </h2>
                        <Button
                            severity="secondary"
                            text
                            rounded
                            size="small"
                            title="Hide details"
                            aria-label="Hide details"
                            @click="isDetailsPanelOpen = false"
                        >
                            <template #icon>
                                <Icon icon="heroicons:chevron-double-right" class="text-base" />
                            </template>
                        </Button>
                    </header>
                </section>
            </SplitterPanel>
        </Splitter>

        <ProjectDocumentCreateDialog
            v-model:visible="tree.createDialog.visible.value"
            v-model:form-data="tree.createDialog.formData.value"
            :validation-errors="tree.createDialog.validationErrors.value"
            :is-pending="tree.createDialog.isPending.value"
            :parent-document="tree.createDialog.parentDocument.value"
            @submit="tree.createDialog.submit"
        />

        <div v-if="!isDetailsPanelOpen" class="pt-2 flex shrink-0 items-start">
            <Button
                severity="secondary"
                text
                rounded
                size="small"
                title="Show details"
                aria-label="Show details"
                @click="isDetailsPanelOpen = true"
            >
                <template #icon>
                    <Icon icon="heroicons:chevron-double-left" class="text-base" />
                </template>
            </Button>
        </div>
    </div>
</template>
