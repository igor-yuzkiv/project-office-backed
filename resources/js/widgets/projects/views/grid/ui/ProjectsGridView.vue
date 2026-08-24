<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import Paginator from 'primevue/paginator'
import Skeleton from 'primevue/skeleton'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { PaginationMeta } from '@/shared/types'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { DisplayDate } from '@/shared/components/display'
import { RouterLink } from 'vue-router'

const props = defineProps<{
    projects: ProjectOverviewDto[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    // Tells the two empty states apart: nothing here yet, or nothing matched.
    isFiltered?: boolean
}>()

const emit = defineEmits<{
    (e: 'page-change', page: number): void
}>()

const perPage = computed(() => props.paginationMeta?.per_page ?? props.projects.length)
const showPaginator = computed(() => !!props.paginationMeta && props.paginationMeta.last_page > 1)

/** Each count opens the section it counts, so the number is also the way in. */
function counters(project: ProjectOverviewDto) {
    return [
        {
            label: 'Docs',
            icon: 'tabler:file-text',
            count: project.docs_count ?? 0,
            to: { name: 'project-documentation', params: { projectId: project.id } },
        },
        {
            label: 'Task Lists',
            icon: 'tabler:list-details',
            count: project.task_lists_count ?? 0,
            to: { name: 'project-details.task-lists', params: { id: project.id } },
        },
        {
            label: 'Tasks',
            icon: 'tabler:circle-check',
            count: project.tasks_count ?? 0,
            to: { name: 'project-details.tasks', params: { id: project.id } },
        },
    ]
}

function onPageChange(event: { page: number }) {
    emit('page-change', event.page + 1)
}
</script>

<template>
    <div v-if="isPending" class="gap-3 p-1 sm:grid-cols-2 xl:grid-cols-3 grid grid-cols-1">
        <Skeleton v-for="n in 6" :key="n" height="9rem" />
    </div>

    <div v-else-if="!projects.length" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center">
        <Icon icon="heroicons:rectangle-stack" class="text-surface-300 text-4xl" />
        <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">
            {{ isFiltered ? 'No projects match these filters' : 'No projects yet' }}
        </p>
        <p class="text-surface-500 max-w-sm text-xs">
            {{
                isFiltered
                    ? 'Try a different search term, or clear the filters to see everything.'
                    : 'Create the first project to get started.'
            }}
        </p>
    </div>

    <template v-else>
        <div class="gap-3 p-1 sm:grid-cols-2 xl:grid-cols-3 grid flex-1 grid-cols-1 content-start overflow-auto">
            <article
                v-for="project in projects"
                :key="project.id"
                class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 gap-3 p-4 rounded-xl flex flex-col border"
            >
                <div class="gap-3 flex items-start">
                    <ProjectIcon :prefix="project.prefix" :icon="project.icon" size="large" class="shrink-0" />

                    <div class="gap-1 min-w-0 flex flex-1 flex-col">
                        <!-- The name is the way into the project; the card itself is not a link,
                             so the counters below stay reachable. -->
                        <div class="gap-2 min-w-0 flex items-baseline">
                            <span class="text-surface-400 text-xs shrink-0">{{ project.prefix }}</span>
                            <RouterLink
                                :to="{ name: 'project-details', params: { id: project.id } }"
                                class="text-surface-900 dark:text-surface-0 hover:text-primary min-w-0 font-semibold truncate"
                                :title="project.name"
                            >
                                {{ project.name }}
                            </RouterLink>
                        </div>

                        <ProjectStatusTag :status="project.status" variant="light" class="w-fit" />
                    </div>

                    <slot name="actions" :project="project" />
                </div>

                <div class="gap-2 flex items-center">
                    <UserAvatar
                        v-if="project.updated_by"
                        :initials="project.updated_by.initials"
                        :avatar-url="project.updated_by.avatar_url"
                        size="small"
                        class="shrink-0"
                    />
                    <DisplayDate label="Updated" :date="project.updated_at" class="text-xs" />
                </div>

                <div class="gap-2 mt-auto grid grid-cols-3">
                    <RouterLink
                        v-for="counter in counters(project)"
                        :key="counter.label"
                        :to="counter.to"
                        class="border-surface-200 dark:border-surface-700 hover:border-surface-300 dark:hover:border-surface-600 gap-1.5 px-2 py-1.5 text-xs rounded-lg flex items-center justify-center border transition-colors"
                    >
                        <Icon :icon="counter.icon" class="text-surface-400 text-sm shrink-0" />
                        <span class="text-surface-700 dark:text-surface-200 truncate">
                            {{ counter.count }} {{ counter.label }}
                        </span>
                    </RouterLink>
                </div>
            </article>
        </div>

        <Paginator
            v-if="showPaginator"
            :rows="perPage"
            :total-records="paginationMeta!.total"
            :first="(page - 1) * perPage"
            @page="onPageChange"
        />
    </template>
</template>
