<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useTaskQuery } from '@/entities/task/queries'
import { useDeleteTaskMutation } from '@/entities/task/mutations'
import { Icon } from '@iconify/vue'
import { DisplayField, CopyToClipboard } from '@/shared/components/display'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TagList } from '@/widgets/tags/metadata'
import { TaskDetailsSidebar } from '@/widgets/tasks/details-sidebar'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
// Navigating between tasks of one list changes only the route parameter, so the router reuses this
// component — everything derived from the id has to be reactive or the page keeps the old task.
const taskId = useRouteParams<string>('id')

const { task, isError } = useTaskQuery(taskId)
const { mutateWithConfirm: deleteTask } = useDeleteTaskMutation()

function handleDeleteTask() {
    deleteTask(taskId.value, `Are you sure you want to delete "${task.value?.name}"?`, () =>
        router.push({ name: 'tasks' })
    )
}

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
    router.push({ name: `task-details.${value}`, params: { id: taskId.value } })
}

useHeaderActions(() => [
    {
        key: 'edit-task',
        title: 'Edit Task',
        to: { name: 'task-edit', params: { id: taskId.value } },
        is_primary: true,
    },
    { key: 'delete-task', title: 'Delete', action: handleDeleteTask },
])

useBreadcrumbs(() => [
    { label: 'Tasks', to: { name: 'tasks' } },
    ...(task.value?.project
        ? [{ label: task.value.project.name, to: { name: 'project-details', params: { id: task.value.project_id } } }]
        : []),
    { label: task.value ? task.value.key : 'Task' },
])
</script>

<template>
    <div v-if="task" class="gap-4 p-2 flex flex-1 overflow-hidden">
        <Tabs :value="activeTab" class="flex flex-1 flex-col overflow-hidden" @update:value="onTabChange">
            <div class="gap-1 p-3 flex shrink-0 flex-col truncate">
                <div class="gap-x-3 flex items-center">
                    <DisplayField v-if="task.project" inline>
                        <ProjectIcon
                            :prefix="task.project.prefix"
                            :icon-emoji="task.project.icon_emoji"
                            size="small"
                            :status="task.project.status"
                        />
                        <RouterLink
                            :to="{ name: 'project-details', params: { id: task.project_id } }"
                            class="text-sm app-link"
                        >
                            {{ task.project.name }}
                        </RouterLink>
                    </DisplayField>

                    <DisplayField v-if="task.task_list" inline>
                        <Icon icon="heroicons:chevron-right" class="text-surface-400" />
                        <RouterLink
                            :to="{ name: 'task-list-details', params: { id: task.task_list.id } }"
                            class="text-sm app-link"
                        >
                            {{ task.task_list.name }}
                        </RouterLink>
                    </DisplayField>
                </div>

                <div class="gap-x-2 text-2xl font-semibold flex items-center truncate">
                    <CopyToClipboard class="text-surface-400" :text="task.key" hide-copy-icon />
                    <h1 class="text-surface-900 dark:text-surface-0 truncate">{{ task.name }}</h1>
                </div>

                <TagList :tags="task.tags ?? []" />
            </div>

            <TabList>
                <Tab value="details" class="px-4 py-2">Details</Tab>
                <Tab value="description" class="px-4 py-2">Description</Tab>
                <Tab value="comments" class="px-4 py-2">Comments ({{ task.comments_count ?? 0 }})</Tab>
                <Tab value="attachments" class="px-4 py-2">Attachments</Tab>
                <Tab value="related-docs" class="px-4 py-2">Related Docs</Tab>
                <Tab value="owners" class="px-4 py-2">Owners</Tab>
            </TabList>

            <div class="min-h-0 flex-1 overflow-auto">
                <router-view v-slot="{ Component }">
                    <transition name="page" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </Tabs>

        <!-- Below lg the column would leave the tab content too narrow to read, so it is dropped
             entirely rather than stacked: everything in it is reachable elsewhere on the page. -->
        <TaskDetailsSidebar :task="task" class="lg:flex hidden" />
    </div>
</template>
