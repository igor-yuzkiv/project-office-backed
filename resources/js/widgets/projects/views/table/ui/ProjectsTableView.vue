<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, type RouteLocationRaw } from 'vue-router'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import { Icon } from '@iconify/vue'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { PROJECT_COUNT_VIEWS, projectTaskCounts } from '@/entities/project/lib'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { IconButton } from '@/shared/components/button'
import { STATUS_COLORS, useStatusColors } from '@/shared/components/status-pill'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { useProjectCardMenu } from '@/widgets/projects/project-card'
import { UserAvatar } from '@/widgets/user/user-avatar'

defineProps<{
    projects: ProjectOverviewDto[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
}>()

const emit = defineEmits<{
    (e: 'edit', project: ProjectOverviewDto): void
    (e: 'delete', project: ProjectOverviewDto): void
    (e: 'pageChange', page: number): void
}>()

const columns: EntityTableColumnDef[] = [
    { field: 'name', header: 'Project', style: 'min-width: 16rem' },
    { field: 'status', header: 'Status', style: 'width: 8rem' },
    { field: 'last_activity', header: 'Last activity', style: 'min-width: 14rem' },
    { field: 'open', header: 'Open', style: 'width: 5rem' },
    { field: 'in_progress', header: 'In progress', style: 'width: 7rem' },
    { field: 'to_test', header: 'To test', style: 'width: 5.5rem' },
    { field: 'tasks', header: 'Tasks', style: 'width: 5rem' },
]

const openColors = useStatusColors(STATUS_COLORS.open)
const progressColors = useStatusColors(STATUS_COLORS.progress)
const testColors = useStatusColors(STATUS_COLORS.test)

const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedProject = ref<ProjectOverviewDto>()
const { items: rowMenuItems } = useProjectCardMenu(selectedProject, {
    onEdit: (project) => emit('edit', project),
    onDelete: (project) => emit('delete', project),
})

function openRowMenu(event: MouseEvent, project: ProjectOverviewDto) {
    selectedProject.value = project
    rowMenu.value?.toggle(event)
}

function projectRoute(project: ProjectOverviewDto): RouteLocationRaw {
    return { name: 'project-details', params: { id: project.id } }
}

function tasksRoute(project: ProjectOverviewDto, view?: string): RouteLocationRaw {
    return { name: 'project-details.tasks', params: { id: project.id }, query: view ? { view } : undefined }
}

function countLabel(total: number | undefined) {
    return total === undefined ? undefined : `${total} ${total === 1 ? 'project' : 'projects'}`
}
</script>

<template>
    <EntityTableView
        :rows="projects"
        :columns="columns"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        :count-label="countLabel(paginationMeta?.total)"
        actions-placement="end"
        :to="projectRoute"
        @page-change="$emit('pageChange', $event)"
    >
        <template #column:name="{ row }">
            <div class="gap-3 min-w-0 flex items-center">
                <ProjectIcon :prefix="row.prefix" :icon="row.icon" size="small" class="shrink-0" />
                <span class="font-medium min-w-0 truncate" :title="row.name">{{ row.name }}</span>
            </div>
        </template>

        <template #column:status="{ row }">
            <ProjectStatusTag :status="row.status" class="w-fit" />
        </template>

        <template #column:last_activity="{ row }">
            <div v-if="row.last_activity" class="gap-1.5 text-ink-2 min-w-0 flex items-center whitespace-nowrap">
                <UserAvatar
                    v-if="row.last_activity.actor"
                    :initials="row.last_activity.actor.initials"
                    :avatar-url="row.last_activity.actor.avatar_url"
                    size="small"
                    class="shrink-0"
                />
                <span v-else class="bg-hover text-ink-3 h-6 w-6 grid shrink-0 place-items-center rounded-full">
                    <Icon icon="tabler:bolt" class="text-[12px]" />
                </span>
                <span class="min-w-0 truncate" :title="row.last_activity.title">{{ row.last_activity.title }}</span>
                <span class="text-ink-3 shrink-0" :title="row.last_activity.created_at">
                    {{ formatRelativeTime(row.last_activity.created_at) }}
                </span>
            </div>
            <div v-else class="gap-1.5 text-ink-2 flex items-center whitespace-nowrap">
                <UserAvatar
                    v-if="row.updated_by"
                    :initials="row.updated_by.initials"
                    :avatar-url="row.updated_by.avatar_url"
                    size="small"
                />
                <span :title="row.updated_at">Updated {{ formatRelativeTime(row.updated_at) }}</span>
            </div>
        </template>

        <template #column:open="{ row }">
            <RouterLink
                :to="tasksRoute(row, PROJECT_COUNT_VIEWS.open)"
                class="font-medium tabular-nums underline-offset-2 hover:underline"
                :style="{ color: openColors.fg }"
                @click.stop
            >
                {{ projectTaskCounts(row.task_status_counts).open }}
            </RouterLink>
        </template>

        <template #column:in_progress="{ row }">
            <span class="font-medium tabular-nums" :style="{ color: progressColors.fg }">
                {{ projectTaskCounts(row.task_status_counts).inProgress }}
            </span>
        </template>

        <template #column:to_test="{ row }">
            <span class="font-medium tabular-nums" :style="{ color: testColors.fg }">
                {{ projectTaskCounts(row.task_status_counts).toTest }}
            </span>
        </template>

        <template #column:tasks="{ row }">
            <RouterLink
                :to="tasksRoute(row, PROJECT_COUNT_VIEWS.tasks)"
                class="text-ink font-medium tabular-nums underline-offset-2 hover:underline"
                @click.stop
            >
                {{ projectTaskCounts(row.task_status_counts).tasks }}
            </RouterLink>
        </template>

        <template #actions="{ row }">
            <div class="gap-1 flex items-center justify-end whitespace-nowrap">
                <Button
                    :as="RouterLink"
                    :to="projectRoute(row)"
                    label="Open"
                    severity="secondary"
                    outlined
                    size="small"
                    class="!h-7"
                    @click.stop
                />
                <Button
                    :as="RouterLink"
                    :to="tasksRoute(row)"
                    label="Tasks"
                    severity="secondary"
                    outlined
                    size="small"
                    class="!h-7"
                    @click.stop
                />
                <IconButton icon="pepicons-pop:dots-x" aria-label="More" @click.stop="openRowMenu($event, row)" />
            </div>
        </template>

        <template #empty>
            <div class="type-meta-3 py-6 text-center">No projects found.</div>
        </template>
    </EntityTableView>

    <Menu ref="rowMenu" :model="rowMenuItems" popup />
</template>
