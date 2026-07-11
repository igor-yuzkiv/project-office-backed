<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { PAGE_SIZE } from '@/app/config'
import { useProjectDocumentsSearchQuery } from '@/entities/project-document/queries'
import type { ProjectDocumentOverviewDto, ProjectDocumentSearchParams } from '@/entities/project-document/types'
import { useTaskProjectDocumentsQuery } from '@/entities/task/queries'
import { useSyncTaskProjectDocumentsMutation } from '@/entities/task/mutations'
import type { FilterPayloadItem } from '@/shared/filters'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyToClipboard } from '@/shared/components/display'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { useToast } from '@/shared/composables/use.toast'

const props = defineProps<{
    taskId: string
    projectId: string
}>()

const visible = defineModel<boolean>('visible', { required: true })

const toast = useToast()

// TODO: Fetches the authoritative full list of already-associated documents (not the page's possibly-paginated view) since Save replaces the entire association in one sync call.
const { projectDocuments: currentDocuments } = useTaskProjectDocumentsQuery(
    () => props.taskId,
    () => ({ page: 1, per_page: 1000 })
)

const draftDocuments = ref<ProjectDocumentOverviewDto[]>([])
const selectedToAdd = ref<ProjectDocumentOverviewDto[]>([])
const searchQuery = ref('')
const page = ref(1)

const debouncedSearchQuery = refDebounced(
    computed(() => searchQuery.value.trim()),
    300
)

const searchParams = computed<ProjectDocumentSearchParams>(() => ({
    query: debouncedSearchQuery.value,
    filters: [
        {
            filter_key: 'lookup',
            field_name: 'project_id',
            value: props.projectId,
            matchMode: null,
            params: {},
        } satisfies FilterPayloadItem,
    ],
    page: page.value,
    per_page: PAGE_SIZE,
}))

const { projectDocuments: searchResults, paginationMeta, isPending } = useProjectDocumentsSearchQuery(searchParams)

const { mutate: syncDocuments, isPending: isSaving } = useSyncTaskProjectDocumentsMutation(() => props.taskId)

const draftDocumentIds = computed(() => new Set(draftDocuments.value.map((d) => d.id)))

const availableDocuments = computed(() => searchResults.value.filter((d) => !draftDocumentIds.value.has(d.id)))

const columns: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'width: 10rem' },
    { field: 'title', header: 'Title' },
    { field: 'status', header: 'Status', style: 'width: 10rem' },
]

// Guards against wiping in-progress user edits: the authoritative currentDocuments fetch
// resolves asynchronously after the dialog opens, so it re-inits the draft at most once
// per open (right when real data arrives), not on every change afterwards.
let hasSyncedCurrentDocuments = false

function initDraft() {
    draftDocuments.value = [...currentDocuments.value]
    selectedToAdd.value = []
    searchQuery.value = ''
    page.value = 1
}

function removeDocument(index: number) {
    draftDocuments.value.splice(index, 1)
}

function addSelected() {
    for (const document of selectedToAdd.value) {
        if (!draftDocumentIds.value.has(document.id)) {
            draftDocuments.value.push(document)
        }
    }
    selectedToAdd.value = []
}

function onCancel() {
    visible.value = false
    initDraft()
}

function onSave() {
    addSelected()

    syncDocuments(
        draftDocuments.value.map((d) => d.id),
        {
            onSuccess() {
                visible.value = false
            },
            onError() {
                toast.error('Failed to associate documents. Please try again.')
            },
        }
    )
}

function onPageChange(newPage: number) {
    page.value = newPage
}

watch(visible, (isVisible) => {
    if (isVisible) {
        hasSyncedCurrentDocuments = false
        initDraft()
    }
})

watch(currentDocuments, () => {
    if (visible.value && !hasSyncedCurrentDocuments) {
        initDraft()
        hasSyncedCurrentDocuments = true
    }
})
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="true" :style="{ width: '55rem' }">
        <template #header>
            <div class="gap-2 flex items-center">
                <i class="pi pi-file text-primary" />
                <span class="text-base font-semibold">Associate Documents</span>
            </div>
        </template>

        <div class="gap-5 py-1 flex flex-col">
            <!-- Associated Documents -->
            <div class="gap-3 flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase">
                        Associated Documents
                    </span>
                    <span class="text-xs text-surface-500">{{ draftDocuments.length }} associated</span>
                </div>

                <div
                    v-if="draftDocuments.length > 0"
                    class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y"
                >
                    <div
                        v-for="(document, index) in draftDocuments"
                        :key="document.id"
                        class="gap-3 py-2 flex items-center"
                    >
                        <CopyToClipboard :text="document.key" hide-copy-icon class="text-surface-500" />
                        <span class="min-w-0 text-sm flex-1 truncate">{{ document.title }}</span>
                        <ProjectDocumentStatusTag :status="document.status" class="w-fit shrink-0" />
                        <Button
                            icon="pi pi-times"
                            severity="secondary"
                            text
                            rounded
                            size="small"
                            class="shrink-0"
                            @click="removeDocument(index)"
                        />
                    </div>
                </div>

                <p v-else class="text-sm text-surface-400">No documents associated yet</p>
            </div>

            <!-- Available Documents -->
            <div class="gap-3 flex flex-col">
                <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase">Available Documents</span>

                <InputText v-model="searchQuery" placeholder="Search documents in this project..." class="w-full" />

                <EntityTableView
                    v-model:selection="selectedToAdd"
                    :rows="availableDocuments"
                    :columns="columns"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    selection-mode="multiple"
                    class="max-h-96"
                    @page-change="onPageChange"
                >
                    <template #column:key="{ row }">
                        <CopyToClipboard :text="row.key" hide-copy-icon class="text-surface-500" />
                    </template>
                    <template #column:status="{ row }">
                        <ProjectDocumentStatusTag :status="row.status" class="w-fit" />
                    </template>
                </EntityTableView>
            </div>
        </div>

        <template #footer>
            <div class="gap-2 flex justify-end">
                <Button label="Cancel" severity="secondary" outlined @click="onCancel" />
                <Button label="Save" :loading="isSaving" @click="onSave" />
            </div>
        </template>
    </Dialog>
</template>
