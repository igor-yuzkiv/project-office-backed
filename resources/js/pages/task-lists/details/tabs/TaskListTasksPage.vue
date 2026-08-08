<script setup lang="ts">
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { useRouteParams } from '@vueuse/router'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { TaskListTasksExpansion } from '@/widgets/task-list/tasks-table'
import { AddTasksToTaskListDialog, useAddTasksToTaskListDialog } from '@/widgets/task-list/add-tasks-dialog'

const taskListId = useRouteParams<string>('id')

const { taskList } = useTaskListQuery(taskListId)

const addTasksDialog = useAddTasksToTaskListDialog(
    () => taskListId.value,
    () => taskList.value?.project_id
)
</script>

<template>
    <div class="flex h-full w-full flex-col overflow-hidden">
        <div class="gap-2 p-1 flex items-center justify-end">
            <Button severity="info" text label="Add Tasks" :disabled="!taskList" @click="addTasksDialog.open()">
                <template #icon>
                    <Icon icon="material-symbols:add" class="text-lg" />
                </template>
            </Button>
        </div>

        <TaskListTasksExpansion :task-list-id="taskListId" />

        <AddTasksToTaskListDialog
            v-model:visible="addTasksDialog.visible.value"
            v-model:selected="addTasksDialog.selected.value"
            v-model:search-query="addTasksDialog.searchQuery.value"
            :candidates="addTasksDialog.candidates.value"
            :pagination-meta="addTasksDialog.paginationMeta.value"
            :page="addTasksDialog.page.value"
            :is-pending="addTasksDialog.isPending.value"
            :is-saving="addTasksDialog.isSaving.value"
            :can-save="addTasksDialog.canSave.value"
            @page-change="addTasksDialog.onPageChange"
            @submit="addTasksDialog.submit()"
        />
    </div>
</template>
