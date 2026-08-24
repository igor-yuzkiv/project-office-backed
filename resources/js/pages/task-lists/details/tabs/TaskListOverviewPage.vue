<script setup lang="ts">
import { useRoute } from 'vue-router'
import Panel from 'primevue/panel'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { MarkdownPreview } from '@/shared/components/md-editor'

const route = useRoute()
const taskListId = route.params.id as string

const { taskList } = useTaskListQuery(taskListId)
</script>

<template>
    <div v-if="taskList" class="gap-4 p-2 flex flex-col">
        <Panel header="Description" :toggleable="true">
            <MarkdownPreview v-if="taskList.description" :model-value="taskList.description" />
            <p v-else class="text-sm text-surface-400 italic">No description available.</p>
        </Panel>
    </div>
</template>
