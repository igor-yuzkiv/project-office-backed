<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { useDeleteTaskListMutation } from '@/entities/task-list/mutations'
import { DisplayField, CopyToClipboard } from '@/shared/components/display'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TagList } from '@/widgets/tags/metadata'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const taskListId = route.params.id as string

const { taskList, isError } = useTaskListQuery(taskListId)
const { mutateWithConfirm: deleteTaskList } = useDeleteTaskListMutation()

function handleDeleteTaskList() {
    deleteTaskList(taskListId, `Are you sure you want to delete "${taskList.value?.name}"?`, () =>
        router.push({ name: 'task-lists' })
    )
}

const activeTab = computed(
    () =>
        String(route.name ?? '')
            .split('.')
            .at(-1) ?? 'overview'
)

watch(isError, (value) => {
    if (value) toast.error('Failed to load task list.')
})

watch(
    taskList,
    (list) => {
        if (list) layoutStore.setPageTitle(`${list.key} | ${list.name}`)
    },
    { immediate: true }
)

function onTabChange(value: string | number) {
    router.push({ name: `task-list-details.${value}`, params: { id: taskListId } })
}

useHeaderActions([
    {
        key: 'edit-task-list',
        title: 'Edit Task List',
        to: { name: 'task-list-edit', params: { id: taskListId } },
        is_primary: true,
    },
    { key: 'delete-task-list', title: 'Delete', action: handleDeleteTaskList },
])

useBreadcrumbs(() => [
    { label: 'Task Lists', to: { name: 'task-lists' } },
    ...(taskList.value?.project
        ? [
              {
                  label: taskList.value.project.name,
                  to: { name: 'project-details', params: { id: taskList.value.project_id } },
              },
          ]
        : []),
    { label: taskList.value ? taskList.value.key : 'Task List' },
])
</script>

<template>
    <div v-if="taskList" class="p-2 flex flex-1 overflow-hidden">
        <Tabs :value="activeTab" class="flex flex-1 flex-col overflow-hidden" @update:value="onTabChange">
            <div class="p-3 flex shrink-0 items-start justify-between truncate">
                <div class="gap-1 flex flex-col truncate">
                    <DisplayField v-if="taskList.project" inline>
                        <ProjectIcon :prefix="taskList.project.prefix" size="small" :status="taskList.project.status" />
                        <RouterLink
                            :to="{ name: 'project-details', params: { id: taskList.project_id } }"
                            class="text-sm app-link"
                        >
                            {{ taskList.project.name }}
                        </RouterLink>
                    </DisplayField>

                    <div class="gap-x-2 text-2xl font-semibold flex items-center truncate">
                        <CopyToClipboard class="text-surface-400" :text="taskList.key" hide-copy-icon />
                        <h1 class="text-surface-900 dark:text-surface-0 truncate">{{ taskList.name }}</h1>
                    </div>

                    <TagList :tags="taskList.tags ?? []" />
                </div>

                <TaskListStatusTag :status="taskList.status" class="w-fit" />
            </div>

            <TabList>
                <Tab value="overview" class="px-4 py-2">Overview</Tab>
                <Tab value="tasks" class="px-4 py-2">Tasks ({{ taskList.tasks_count ?? 0 }})</Tab>
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
