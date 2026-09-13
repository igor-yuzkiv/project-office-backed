<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useLocalStorage } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import { useProjectQuery } from '@/entities/project/queries'
import { useProjectAttachmentsDialog } from '@/entities/project/composables'
import { PROJECT_COUNT_VIEWS, projectTaskCounts } from '@/entities/project/lib'
import { useTaskListsSearchQuery } from '@/entities/task-list/queries'
import { taskListTableColumnsExcluding } from '@/entities/task-list/config'
import type { ITaskList, TaskListSearchParams } from '@/entities/task-list/types'
import { useTasksSearchQuery } from '@/entities/task/queries'
import { taskTableColumnsExcluding } from '@/entities/task/config'
import type { TaskOverviewDto, TaskSearchParams } from '@/entities/task/types'
import { useTaskViewsQuery } from '@/entities/task-view'
import type { FilterPayloadItem } from '@/shared/filters'
import { PropertiesGrid } from '@/shared/components/display'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { formatDate } from '@/shared/utils/date.util'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { AttachmentsDialog } from '@/widgets/attachments/attachments-dialog'
import { TagList } from '@/widgets/tags/metadata'
import { TaskListsTableView } from '@/widgets/task-list/views/table'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { UserAvatar } from '@/widgets/user/user-avatar'

const route = useRoute()
const projectId = route.params.id as string

const { project } = useProjectQuery(projectId)
const attachmentsDialog = useProjectAttachmentsDialog(projectId)
const { views: taskViews, isPending: isTaskViewsPending } = useTaskViewsQuery()

const showAttachmentsDialog = ref(false)
const descriptionCollapsed = useLocalStorage('app:project:description-collapsed', false)

const counts = computed(() => projectTaskCounts(project.value?.task_status_counts))

const tasksTabRoute = (view: string) => ({
    name: 'project-details.tasks',
    params: { id: projectId },
    query: { view },
})

const stats = computed(() => [
    { label: 'In progress', value: counts.value.inProgress, accent: true },
    { label: 'Ready to test', value: counts.value.toTest },
    { label: 'All open', value: counts.value.open, to: tasksTabRoute(PROJECT_COUNT_VIEWS.open) },
    { label: 'Backlog', value: counts.value.backlog, to: tasksTabRoute(PROJECT_COUNT_VIEWS.backlog) },
    { label: 'Closed', value: counts.value.closed, to: tasksTabRoute(PROJECT_COUNT_VIEWS.closed) },
    { label: 'Attachments', value: attachmentsDialog.count.value, onClick: () => (showAttachmentsDialog.value = true) },
])

// Task lists and tasks register the project field under different filters.
const taskListProjectFilter: FilterPayloadItem = {
    filter_key: 'text',
    field_name: 'project_id',
    value: projectId,
    matchMode: 'equals',
    params: {},
}
const taskProjectFilter: FilterPayloadItem = {
    filter_key: 'lookup',
    field_name: 'project_id',
    value: projectId,
    matchMode: null,
    params: {},
}

const taskListColumns = taskListTableColumnsExcluding('project')
const taskListsParams: TaskListSearchParams = {
    filters: [taskListProjectFilter],
    sort_by: 'updated_at',
    sort_order: 'desc',
    page: 1,
    per_page: 3,
    include: ['updatedBy'],
}
const { taskLists, isPending: isTaskListsPending } = useTaskListsSearchQuery(taskListsParams)

// The open statuses are whatever the "all open" view says they are, so the two never drift apart.
const openView = computed(() => taskViews.value.find((view) => view.key === PROJECT_COUNT_VIEWS.open))

const taskColumns = taskTableColumnsExcluding('project', 'tags')
const recentTasksParams = computed<TaskSearchParams>(() => ({
    filters: [taskProjectFilter, ...(openView.value?.filters ?? [])],
    sort_by: 'updated_at',
    sort_order: 'desc',
    page: 1,
    per_page: 5,
    include: ['taskList', 'updatedBy'],
}))
const { tasks: recentTasks, isPending: isRecentTasksPending } = useTasksSearchQuery(recentTasksParams, {
    enabled: computed(() => !isTaskViewsPending.value),
})

const dates = computed(() => {
    const p = project.value
    if (!p?.start_date && !p?.end_date) return null
    return `${formatDate(p.start_date) ?? '—'} → ${formatDate(p.end_date) ?? '—'}`
})

function taskListDetailsRoute(taskList: ITaskList) {
    return { name: 'task-list-details', params: { id: taskList.id } }
}

function taskDetailsRoute(task: TaskOverviewDto) {
    return { name: 'task-details', params: { id: task.id } }
}
</script>

<template>
    <div v-if="project">
        <div class="border-line divide-line rounded-lg sm:grid-cols-6 grid grid-cols-3 divide-x overflow-hidden border">
            <component
                :is="stat.to ? RouterLink : stat.onClick ? 'button' : 'div'"
                v-for="stat in stats"
                :key="stat.label"
                :to="stat.to"
                :type="stat.onClick ? 'button' : undefined"
                class="gap-0.5 px-4 py-3.5 flex flex-col text-left"
                :class="stat.to || stat.onClick ? 'hover:bg-hover cursor-pointer' : ''"
                @click="stat.onClick"
            >
                <b
                    class="leading-tight font-semibold tracking-tight text-[22px] tabular-nums"
                    :class="stat.accent ? 'text-accent' : 'text-ink'"
                >
                    {{ stat.value }}
                </b>
                <span class="type-meta">{{ stat.label }}</span>
            </component>
        </div>

        <h2 class="type-section gap-2 mt-8 mb-2.5 flex items-baseline">
            Task lists
            <RouterLink
                :to="{ name: 'project-details.task-lists', params: { id: projectId } }"
                class="text-ink-2 hover:text-accent font-normal ml-auto text-[13px]"
            >
                All {{ project.task_lists_count ?? 0 }}
            </RouterLink>
        </h2>
        <TaskListsTableView
            :task-lists="taskLists"
            :is-pending="isTaskListsPending"
            :page="1"
            :columns="taskListColumns"
            :to="taskListDetailsRoute"
        />

        <h2 class="type-section gap-2 mt-8 mb-2.5 flex items-baseline">
            Recent tasks
            <RouterLink
                :to="tasksTabRoute(PROJECT_COUNT_VIEWS.open)"
                class="text-ink-2 hover:text-accent font-normal ml-auto text-[13px]"
            >
                All {{ counts.open }} open
            </RouterLink>
        </h2>
        <TasksTableView
            :tasks="recentTasks"
            :is-pending="isRecentTasksPending"
            :page="1"
            :columns="taskColumns"
            :to="taskDetailsRoute"
        />

        <h2 class="type-section mt-8 mb-2.5">About</h2>
        <PropertiesGrid>
            <span>Dates</span>
            <div>
                <span class="text-ink-2 text-[13px]">{{ dates ?? '—' }}</span>
            </div>

            <span>Tags</span>
            <div>
                <TagList v-if="project.tags?.length" :tags="project.tags" inline />
                <span v-else class="type-meta-3">No tags</span>
            </div>

            <span>Created</span>
            <div>
                <UserAvatar
                    v-if="project.created_by"
                    :initials="project.created_by.initials"
                    :avatar-url="project.created_by.avatar_url"
                    size="xsmall"
                />
                <span class="text-ink-2 text-[13px]">
                    {{ project.created_by?.name ?? 'Unknown' }}
                    <span class="text-ink-3">· {{ formatRelativeTime(project.created_at) }}</span>
                </span>
            </div>

            <span>Updated</span>
            <div>
                <UserAvatar
                    v-if="project.updated_by"
                    :initials="project.updated_by.initials"
                    :avatar-url="project.updated_by.avatar_url"
                    size="xsmall"
                />
                <span class="text-ink-2 text-[13px]">
                    {{ project.updated_by?.name ?? 'Unknown' }}
                    <span class="text-ink-3">· {{ formatRelativeTime(project.updated_at) }}</span>
                </span>
            </div>

            <template v-if="project.archived_at">
                <span>Archived</span>
                <div>
                    <UserAvatar
                        v-if="project.archived_by"
                        :initials="project.archived_by.initials"
                        :avatar-url="project.archived_by.avatar_url"
                        size="xsmall"
                    />
                    <span class="text-ink-2 text-[13px]">
                        {{ project.archived_by?.name ?? 'Unknown' }}
                        <span class="text-ink-3">· {{ formatRelativeTime(project.archived_at) }}</span>
                    </span>
                </div>
            </template>
        </PropertiesGrid>

        <template v-if="project.description">
            <h2 class="type-section mt-8 mb-2.5 flex items-baseline">
                <button
                    type="button"
                    class="gap-1.5 hover:text-ink-2 inline-flex cursor-pointer items-center"
                    :aria-expanded="!descriptionCollapsed"
                    aria-controls="project-description"
                    @click="descriptionCollapsed = !descriptionCollapsed"
                >
                    <Icon
                        :icon="descriptionCollapsed ? 'heroicons:chevron-right' : 'heroicons:chevron-down'"
                        class="text-ink-3 text-[12px]"
                    />
                    Description
                </button>
            </h2>
            <MarkdownPreview
                v-show="!descriptionCollapsed"
                id="project-description"
                :model-value="project.description"
                class="type-prose"
            />
        </template>

        <AttachmentsDialog
            v-model:visible="showAttachmentsDialog"
            :attachments="attachmentsDialog.attachments.value"
            :is-pending="attachmentsDialog.isPending.value"
            :is-uploading="attachmentsDialog.isUploading.value"
            :upload="attachmentsDialog.upload"
            :subtitle="project.prefix"
            @download="attachmentsDialog.download"
            @delete="attachmentsDialog.remove"
        />
    </div>
</template>
