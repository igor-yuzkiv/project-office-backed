<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useTaskQuery } from '@/entities/task/queries'
import { DisplayField, CopyToClipboard } from '@/shared/components/display'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const taskId = route.params.id as string

const { task, isError } = useTaskQuery(taskId)

const activeTab = computed(
    () =>
        String(route.name ?? '')
            .split('.')
            .at(-1) ?? 'description'
)

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

function onTabChange(value: string | number) {
    router.push({ name: `task-details.${value}`, params: { id: taskId } })
}

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
        <Tabs :value="activeTab" class="app-card flex flex-1 flex-col overflow-hidden" @update:value="onTabChange">
            <div class="p-3 flex shrink-0 items-start justify-between">
                <div class="gap-1 flex flex-col">
                    <div class="gap-x-3 flex items-center">
                        <DisplayField v-if="task.project" inline>
                            <ProjectIcon :prefix="task.project.prefix" size="small" />
                            <RouterLink
                                :to="{ name: 'project-details', params: { id: task.project_id } }"
                                class="text-sm app-link"
                                >{{ task.project.name }}</RouterLink
                            >
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
            </TabList>

            <div class="min-h-0 flex-1 overflow-auto">
                <router-view v-slot="{ Component }">
                    <transition name="page" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </Tabs>
    </div>
</template>
