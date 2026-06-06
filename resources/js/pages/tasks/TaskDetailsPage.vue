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
import { DisplayField, DisplayDate, CopyToClipboard } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'
import { ProjectIcon } from '@/widgets/projects/project-icon'
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
    <div v-if="task" class="p-2 flex flex-1 overflow-hidden">
        <Tabs value="description" class="app-card flex flex-1 flex-col overflow-hidden">
            <div class="p-3 flex shrink-0 items-start justify-between">
                <div class="gap-1 flex flex-col">
                    <div class="gap-x-3 flex items-center">
                        <DisplayField v-if="task.project" inline>
                            <ProjectIcon :prefix="task.project.prefix" size="xsmall" />
                            <span class="text-sm text-surface-500">{{ task.project.name }}</span>
                        </DisplayField>

                        <DisplayField v-if="task.task_list" label="Task List" :value="task.task_list.name" inline />
                    </div>

                    <div class="gap-x-2 text-2xl font-semibold flex items-center">
                        <CopyToClipboard class="text-surface-400" :text="task.key" hide-copy-icon />
                        <h1 class="text-surface-900">{{ task.name }}</h1>
                    </div>
                </div>

                <div class="gap-2 flex items-center">
                    <TaskStatusTag :status="task.status" class="w-fit" show-icon />
                    <TaskPriorityTag :priority="task.priority" class="w-fit" />
                </div>
            </div>

            <TabList>
                <Tab value="details" class="px-4 py-2">Details</Tab>
                <Tab value="description" class="px-4 py-2">Description</Tab>
                <Tab value="comments" class="px-4 py-2">Comments</Tab>
                <Tab value="attachments" class="px-4 py-2">Attachments</Tab>
                <Tab value="activity" class="px-4 py-2">Activity</Tab>
                <Tab value="documentation" class="px-4 py-2">Documentation</Tab>
            </TabList>

            <TabPanels class="min-h-0 flex-1 overflow-auto">
                <TabPanel value="details">
                    <div class="gap-4 grid grid-cols-2">
                        <DisplayField label="Key" :value="task.key" />
                        <DisplayField label="Sequence Number" :value="String(task.sequence_number)" />
                        <DisplayField label="Status">
                            <TaskStatusTag :status="task.status" class="w-fit" show-icon />
                        </DisplayField>
                        <DisplayField label="Priority">
                            <TaskPriorityTag :priority="task.priority" class="w-fit" />
                        </DisplayField>
                        <DisplayField
                            label="Project"
                            :value="task.project ? `${task.project.prefix} - ${task.project.name}` : null"
                        />
                        <DisplayField label="Task List" :value="task.task_list?.name ?? null" />
                        <DisplayField label="Created By">
                            <div v-if="task.created_by" class="gap-2 flex items-center">
                                <UserAvatar :user="task.created_by" size="small" />
                                <span class="text-surface-700">{{ task.created_by.name }}</span>
                            </div>
                        </DisplayField>
                        <DisplayField label="Updated By">
                            <div v-if="task.updated_by" class="gap-2 flex items-center">
                                <UserAvatar :user="task.updated_by" size="small" />
                                <span class="text-surface-700">{{ task.updated_by.name }}</span>
                            </div>
                        </DisplayField>
                        <DisplayDate :date="task.created_at" label="Created" />
                        <DisplayDate :date="task.updated_at" label="Updated" />
                    </div>
                </TabPanel>

                <TabPanel value="description">
                    <MarkdownPreview v-if="task.description" :model-value="task.description" />
                    <p v-else class="text-sm text-surface-400 italic">No description available.</p>
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
