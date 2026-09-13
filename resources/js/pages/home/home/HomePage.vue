<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { useBreadcrumbs } from '@/app/shell'
import { greetingFor, useDashboardQuery } from '@/entities/dashboard'
import { usePinnedProjectsQuery } from '@/entities/project/queries'
import { useDeleteProjectMutation } from '@/entities/project/mutations'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { taskTableColumnsExcluding } from '@/entities/task/config'
import type { TaskOverviewDto } from '@/entities/task/types'
import type { ITaskList } from '@/entities/task-list/types'
import { formatDate } from '@/shared/utils/date.util'
import { ProjectCard } from '@/widgets/projects/project-card'
import { TaskListsTableView } from '@/widgets/task-list/views/table'
import { TasksTableView } from '@/widgets/tasks/views/table'

useBreadcrumbs([{ label: 'Home' }])

const router = useRouter()
const authStore = useAuthStore()
const { recentTasks, recentTaskLists, isPending } = useDashboardQuery()
const { projects: pinnedProjects } = usePinnedProjectsQuery()
const { mutateWithConfirm: deleteProject } = useDeleteProjectMutation()

const today = new Date()
const dateLabel = formatDate(today, 'EEEE, MMMM d')
const greeting = computed(() => greetingFor(today, authStore.user?.name))

const taskColumns = taskTableColumnsExcluding('project', 'priority', 'tags')

function onEdit(project: ProjectOverviewDto) {
    router.push({ name: 'project-edit', params: { id: project.id } })
}

function onDelete(project: ProjectOverviewDto) {
    deleteProject(project.id, `Are you sure you want to delete "${project.name}"?`)
}

function taskDetailsRoute(task: TaskOverviewDto) {
    return { name: 'task-details', params: { id: task.id } }
}

function taskListDetailsRoute(taskList: ITaskList) {
    return { name: 'task-list-details', params: { id: taskList.id } }
}
</script>

<template>
    <div class="min-h-0 flex-1 overflow-auto">
        <div class="page-container">
            <div class="mb-6">
                <div class="type-meta">{{ dateLabel }}</div>
                <h1 class="type-title text-ink mt-0.5">{{ greeting }}</h1>
            </div>

            <h2 class="type-section gap-2 mb-2.5 flex items-baseline">
                Pinned projects
                <RouterLink
                    :to="{ name: 'projects' }"
                    class="text-ink-2 hover:text-accent font-normal ml-auto text-[13px]"
                >
                    All projects
                </RouterLink>
            </h2>
            <div v-if="pinnedProjects.length" class="gap-3 lg:grid-cols-2 grid grid-cols-1">
                <ProjectCard
                    v-for="project in pinnedProjects"
                    :key="project.id"
                    :project="project"
                    @edit="onEdit"
                    @delete="onDelete"
                />
            </div>
            <div v-else class="type-meta-3 py-6 text-center">
                Pin a project to see it here —
                <RouterLink :to="{ name: 'projects' }" class="hover:text-accent underline underline-offset-2">
                    Projects
                </RouterLink>
            </div>

            <h2 class="type-section gap-2 mt-8 mb-2.5 flex items-baseline">
                Recent tasks
                <RouterLink
                    :to="{ name: 'tasks' }"
                    class="text-ink-2 hover:text-accent font-normal ml-auto text-[13px]"
                >
                    All tasks
                </RouterLink>
            </h2>
            <TasksTableView
                :tasks="recentTasks"
                :is-pending="isPending"
                :page="1"
                :columns="taskColumns"
                :to="taskDetailsRoute"
            />

            <h2 class="type-section gap-2 mt-8 mb-2.5 flex items-baseline">
                Recent task lists
                <RouterLink
                    :to="{ name: 'task-lists' }"
                    class="text-ink-2 hover:text-accent font-normal ml-auto text-[13px]"
                >
                    All lists
                </RouterLink>
            </h2>
            <TaskListsTableView
                :task-lists="recentTaskLists"
                :is-pending="isPending"
                :page="1"
                :to="taskListDetailsRoute"
            />
        </div>
    </div>
</template>
