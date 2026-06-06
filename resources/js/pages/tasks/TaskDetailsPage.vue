<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { MarkdownPreview } from '@/shared/components/md-editor'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabPanel from 'primevue/tabpanel'
import TabPanels from 'primevue/tabpanels'
import Tabs from 'primevue/tabs'
import { useTaskQuery } from '@/entities/task/queries'
import { DisplayField } from '@/shared/components/display'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

const route = useRoute()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const taskId = route.params.id as string

const { task, isError } = useTaskQuery(taskId)

watch(isError, (error) => {
    if (error) toast.error('Failed to load task.')
})

watch(
    task,
    (t) => {
        if (t) layoutStore.setPageTitle(`${t.key} | ${t.name}`)
    },
    { immediate: true }
)

onMounted(() => {
    layoutStore.setHeaderActions([
        { key: 'edit-task', title: 'Edit Task', to: { name: 'task-edit', params: { id: taskId } }, is_primary: true },
    ])
})

onUnmounted(() => {
    layoutStore.clearHeaderActions()
})
</script>

<template>
    <div v-if="task" class="p-2 gap-2 flex flex-1 overflow-hidden">
        <Tabs value="description" class="app-card flex flex-1 flex-col overflow-hidden">
            <div class="p-3 flex flex-col">
                <div class="gap-x-2 flex items-center">
                    <TaskStatusTag :status="task.status" class="w-fit" show-icon />
                    <TaskPriorityTag :priority="task.priority" class="w-fit" />
                </div>

                <h1 class="text-2xl font-semibold text-surface-900">{{ task.name }}</h1>

                <div class="gap-x-1 flex items-center">
                    <DisplayField v-if="task.project" label="Project" :value="task.project.name" inline />
                    <DisplayField v-if="task.task_list" label="Task List" :value="task.task_list.name ?? null" inline />
                </div>
            </div>

            <TabList>
                <Tab value="description" class="px-4 py-2">Description</Tab>
                <Tab value="comments" class="px-4 py-2">Comments</Tab>
                <Tab value="attachments" class="px-4 py-2">Attachments</Tab>
                <Tab value="activity" class="p-2">Activity</Tab>
                <Tab value="documentation" class="px-4 py-2">Documentation</Tab>
            </TabList>

            <TabPanels class="min-h-0 flex-1 overflow-auto">
                <TabPanel value="description">
                    <div class="flex flex-col">
                        <MarkdownPreview v-if="task.description" :model-value="task.description" />
                        <p v-else class="text-sm text-surface-400 italic">No description available.</p>
                    </div>
                </TabPanel>

                <TabPanel value="comments">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>

                <TabPanel value="attachments">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>
                <TabPanel value="activity">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>
                <TabPanel value="documentation">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>
            </TabPanels>
        </Tabs>
    </div>
</template>
