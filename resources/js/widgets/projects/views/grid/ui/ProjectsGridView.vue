<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import Skeleton from 'primevue/skeleton'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { PaginationMeta } from '@/shared/types'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { DisplayDate } from '@/shared/components/display'

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
                    <ProjectIcon
                        :prefix="project.prefix"
                        :icon-emoji="project.icon_emoji"
                        :status="project.status"
                        size="medium"
                        class="shrink-0"
                    />

                    <div class="min-w-0 flex-1">
                        <h3 class="text-surface-900 dark:text-surface-0 font-semibold truncate" :title="project.name">
                            {{ project.name }}
                        </h3>
                        <span class="text-surface-400 text-xs">{{ project.prefix }}</span>
                    </div>

                    <slot name="actions" :project="project" />
                </div>

                <div class="gap-2 flex items-center justify-between">
                    <ProjectStatusTag :status="project.status" />
                    <span class="text-surface-400 text-xs"> Updated <DisplayDate :date="project.updated_at" /> </span>
                </div>

                <div class="border-surface-200 dark:border-surface-700 gap-2 pt-3 mt-auto flex border-t">
                    <Button
                        label="Details"
                        size="small"
                        severity="secondary"
                        outlined
                        class="flex-1"
                        :as="'router-link'"
                        :to="{ name: 'project-details', params: { id: project.id } }"
                    />
                    <Button
                        label="Documentation"
                        size="small"
                        severity="secondary"
                        text
                        class="flex-1"
                        :as="'router-link'"
                        :to="{ name: 'project-documentation', params: { projectId: project.id } }"
                    />
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
