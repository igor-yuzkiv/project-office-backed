<script setup lang="ts">
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import type { PaginationMeta } from '@/shared/types'
import type { TaskOverviewDto } from '@/entities/task/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyToClipboard } from '@/shared/components/display'
import { SearchInput } from '@/shared/components/input'
import { TaskStatusTag } from '@/widgets/tasks/metadata'

defineProps<{
    candidates: TaskOverviewDto[]
    paginationMeta?: PaginationMeta
    page: number
    isPending: boolean
    isSaving: boolean
    canSave: boolean
}>()

const emit = defineEmits<{
    pageChange: [page: number]
    submit: []
}>()

const visible = defineModel<boolean>('visible', { required: true })
const selected = defineModel<TaskOverviewDto[]>('selected', { required: true })
const searchQuery = defineModel<string>('searchQuery', { required: true })

const columns: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'width: 8rem' },
    { field: 'name', header: 'Task Name' },
    { field: 'status', header: 'Status', style: 'width: 9rem' },
]
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="!isSaving" :style="{ width: '50rem' }" header="Add Tasks">
        <div class="gap-3 py-1 flex flex-col">
            <p class="text-sm text-surface-500">
                Only tasks of this project that do not belong to a list yet can be added.
            </p>

            <SearchInput v-model="searchQuery" placeholder="Search tasks..." />

            <EntityTableView
                v-model:selection="selected"
                :rows="candidates"
                :columns="columns"
                :is-pending="isPending"
                :pagination-meta="paginationMeta"
                :page="page"
                selection-mode="multiple"
                class="max-h-96"
                @page-change="emit('pageChange', $event)"
            >
                <template #column:key="{ row }">
                    <CopyToClipboard :text="row.key" hide-copy-icon class="text-surface-500" />
                </template>

                <template #column:status="{ row }">
                    <TaskStatusTag :status="row.status" class="w-fit" />
                </template>

                <template #empty>
                    <div class="py-6 text-sm text-surface-400 text-center">
                        No tasks available to add. Every task of this project already belongs to a list.
                    </div>
                </template>
            </EntityTableView>
        </div>

        <template #footer>
            <div class="gap-2 flex justify-end">
                <Button label="Cancel" severity="secondary" text :disabled="isSaving" @click="visible = false" />
                <Button
                    :label="selected.length > 0 ? `Add ${selected.length} task(s)` : 'Add'"
                    :loading="isSaving"
                    :disabled="!canSave"
                    @click="emit('submit')"
                />
            </div>
        </template>
    </Dialog>
</template>
