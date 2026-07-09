<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { PAGE_SIZE } from '@/app/config'
import { useTasksSearchQuery } from '@/entities/task/queries'
import { useProjectDocumentTasksQuery, useSyncProjectDocumentTasksMutation } from '@/entities/project-document'
import type { TaskOverviewDto, TaskSearchParams } from '@/entities/task/types'
import type { FilterPayloadItem } from '@/shared/filters'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyToClipboard } from '@/shared/components/display'
import { TaskStatusTag } from '@/widgets/tasks/metadata'
import { useToast } from '@/shared/composables/use.toast'

const props = defineProps<{
    documentId: string
    projectId: string
}>()

const visible = defineModel<boolean>('visible', { required: true })

const toast = useToast()

// Fetches the authoritative full list of already-associated tasks (not the page's
// possibly-paginated view) since Save replaces the entire association in one sync call.
const { tasks: currentTasks } = useProjectDocumentTasksQuery(
    () => props.documentId,
    () => ({ page: 1, per_page: 1000 })
)

const draftTasks = ref<TaskOverviewDto[]>([])
const selectedToAdd = ref<TaskOverviewDto[]>([])
const searchQuery = ref('')
const page = ref(1)

const debouncedSearchQuery = refDebounced(
    computed(() => searchQuery.value.trim()),
    300
)

const searchParams = computed<TaskSearchParams>(() => ({
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

const { tasks: searchResults, paginationMeta, isPending } = useTasksSearchQuery(searchParams)

const { mutate: syncTasks, isPending: isSaving } = useSyncProjectDocumentTasksMutation(() => props.documentId)

const draftTaskIds = computed(() => new Set(draftTasks.value.map((t) => t.id)))

const availableTasks = computed(() => searchResults.value.filter((t) => !draftTaskIds.value.has(t.id)))

const columns: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'width: 8rem' },
    { field: 'name', header: 'Task Name' },
    { field: 'status', header: 'Status', style: 'width: 9rem' },
]

function initDraft() {
    draftTasks.value = [...currentTasks.value]
    selectedToAdd.value = []
    searchQuery.value = ''
    page.value = 1
}

function removeTask(index: number) {
    draftTasks.value.splice(index, 1)
}

function addSelected() {
    for (const task of selectedToAdd.value) {
        if (!draftTaskIds.value.has(task.id)) {
            draftTasks.value.push(task)
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

    syncTasks(
        draftTasks.value.map((t) => t.id),
        {
            onSuccess() {
                visible.value = false
            },
            onError() {
                toast.error('Failed to associate tasks. Please try again.')
            },
        }
    )
}

function onPageChange(newPage: number) {
    page.value = newPage
}

watch(visible, (isVisible) => {
    if (isVisible) initDraft()
})

watch(currentTasks, () => {
    if (visible.value) initDraft()
})
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="true" :style="{ width: '55rem' }">
        <template #header>
            <div class="gap-2 flex items-center">
                <i class="pi pi-file text-primary" />
                <span class="text-base font-semibold">Associate Tasks</span>
            </div>
        </template>

        <div class="gap-5 py-1 flex flex-col">
            <!-- Associated Tasks -->
            <div class="gap-3 flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase">
                        Associated Tasks
                    </span>
                    <span class="text-xs text-surface-500">{{ draftTasks.length }} associated</span>
                </div>

                <div
                    v-if="draftTasks.length > 0"
                    class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y"
                >
                    <div v-for="(task, index) in draftTasks" :key="task.id" class="gap-3 py-2 flex items-center">
                        <CopyToClipboard :text="task.key" hide-copy-icon class="text-surface-500" />
                        <span class="min-w-0 text-sm flex-1 truncate">{{ task.name }}</span>
                        <TaskStatusTag :status="task.status" class="w-fit shrink-0" />
                        <Button
                            icon="pi pi-times"
                            severity="secondary"
                            text
                            rounded
                            size="small"
                            class="shrink-0"
                            @click="removeTask(index)"
                        />
                    </div>
                </div>

                <p v-else class="text-sm text-surface-400">No tasks associated yet</p>
            </div>

            <!-- Available Tasks -->
            <div class="gap-3 flex flex-col">
                <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase">Available Tasks</span>

                <InputText v-model="searchQuery" placeholder="Search tasks in this project..." class="w-full" />

                <EntityTableView
                    v-model:selection="selectedToAdd"
                    :rows="availableTasks"
                    :columns="columns"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    selection-mode="multiple"
                    class="max-h-64"
                    @page-change="onPageChange"
                >
                    <template #column:key="{ row }">
                        <CopyToClipboard :text="row.key" hide-copy-icon class="text-surface-500" />
                    </template>
                    <template #column:status="{ row }">
                        <TaskStatusTag :status="row.status" class="w-fit" />
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
