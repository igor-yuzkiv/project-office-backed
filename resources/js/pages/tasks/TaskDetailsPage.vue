<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { MarkdownPreview } from '@/shared/components/md-editor'
import Panel from 'primevue/panel'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabPanel from 'primevue/tabpanel'
import TabPanels from 'primevue/tabpanels'
import Tabs from 'primevue/tabs'
import { useTaskQuery } from '@/entities/task/queries'
import { DisplayField } from '@/shared/components/display'
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
    <div v-if="task" class="p-6 gap-6 flex flex-1 flex-col overflow-auto">
        <div class="flex flex-col">
            <h1 class="text-2xl font-semibold text-surface-900">{{ task.name }}</h1>
        </div>

        <Panel header="Task Information" toggleable>
            <div class="md:grid-cols-3 gap-4 grid grid-cols-2">
                <DisplayField
                    label="Project"
                    :value="task.project ? `${task.project.prefix} - ${task.project.name}` : null"
                />
                <DisplayField label="Task List" :value="task.task_list?.name ?? null" />
                <DisplayField label="Status" :value="task.status" />
                <DisplayField label="Priority" :value="task.priority?.name ?? null" />
            </div>
        </Panel>

        <Panel header="Description" toggleable>
            <MarkdownPreview v-if="task.description" :model-value="task.description" />
            <p v-else class="text-sm text-surface-400 italic">No description available.</p>
        </Panel>

        <Tabs value="comments">
            <TabList>
                <Tab value="comments">Comments</Tab>
                <Tab value="attachments">Attachments</Tab>
                <Tab value="activity">Activity</Tab>
                <Tab value="documentation">Documentation</Tab>
            </TabList>
            <TabPanels>
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
