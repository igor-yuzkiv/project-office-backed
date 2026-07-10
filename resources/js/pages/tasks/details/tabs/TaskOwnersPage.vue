<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { useTaskOwnersQuery } from '@/entities/task-owner/queries'
import { ManageTaskOwnersDialog } from '@/widgets/task-owners/manage-dialog'
import { TaskOwnersTable } from '@/widgets/task-owners/owners-table'

const route = useRoute()
const taskId = route.params.id as string

const { owners, isPending: isOwnersLoading } = useTaskOwnersQuery(taskId)

const isManageOwnersDialogVisible = ref(false)
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-1 flex justify-end">
            <Button label="Assign" severity="info" size="small" text @click="isManageOwnersDialogVisible = true">
                <template #icon>
                    <Icon icon="material-symbols:add" class="text-lg" />
                </template>
            </Button>
        </div>

        <div class="flex h-full w-full flex-col overflow-hidden">
            <TaskOwnersTable :owners="owners" :is-pending="isOwnersLoading" />
        </div>

        <ManageTaskOwnersDialog v-model:visible="isManageOwnersDialogVisible" :task-id="taskId" />
    </div>
</template>
