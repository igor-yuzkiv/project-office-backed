<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import type { TaskStatusValue } from '@/entities/task/types'
import TaskBulkStatusDialog from './TaskBulkStatusDialog.vue'

const props = defineProps<{
    selectedCount: number
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'apply', status: TaskStatusValue): void
    (e: 'clear'): void
}>()

const isStatusDialogVisible = ref(false)
const status = ref<TaskStatusValue>()

function apply() {
    if (status.value) {
        emit('apply', status.value)
    }
}
</script>

<template>
    <div
        class="gap-3 px-3 py-2 rounded-border bg-surface-100 dark:bg-surface-800 flex items-center"
        data-testid="task-bulk-actions"
    >
        <span class="text-sm text-surface-700 dark:text-surface-300">{{ props.selectedCount }} selected</span>

        <Button label="Update Status" size="small" @click="isStatusDialogVisible = true" />

        <Button label="Clear" size="small" severity="secondary" text @click="emit('clear')" />

        <TaskBulkStatusDialog
            v-model:visible="isStatusDialogVisible"
            v-model:status="status"
            :selected-count="props.selectedCount"
            :is-pending="props.isPending"
            @apply="apply"
        />
    </div>
</template>
