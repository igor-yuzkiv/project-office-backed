<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { useDeleteTaskListMutation } from '@/entities/task-list/mutations'
import { DisplayField, CopyToClipboard } from '@/shared/components/display'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { useToast, useCollapsibleSidePanel } from '@/shared/composables'
import { SidePanel } from '@/shared/components/side-panel'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TaskListDetailsSidebar } from '@/widgets/task-list/details-sidebar'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const taskListId = route.params.id as string

const { taskList, isError } = useTaskListQuery(taskListId)
const sidebarPanel = useCollapsibleSidePanel('task-lists:sidebar-collapsed')
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
    <div v-if="taskList" class="gap-4 p-2 flex flex-1 overflow-hidden">
        <Tabs :value="activeTab" class="flex flex-1 flex-col overflow-hidden" @update:value="onTabChange">
            <div class="gap-1 p-3 flex shrink-0 flex-col truncate">
                <DisplayField v-if="taskList.project" inline>
                    <ProjectIcon :prefix="taskList.project.prefix" :icon="taskList.project.icon" size="small" />
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
            </div>

            <TabList>
                <Tab value="overview" class="px-4 py-2">Overview</Tab>
                <Tab value="tasks" class="px-4 py-2">Tasks</Tab>
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

        <SidePanel
            :panel="sidebarPanel"
            side="right"
            width="28rem"
            icon="heroicons:bars-3-bottom-right"
            show-label="Show task list details"
        >
            <template #default="{ collapse }">
                <TaskListDetailsSidebar :task-list="taskList" @collapse="collapse" />
            </template>
        </SidePanel>
    </div>
</template>
