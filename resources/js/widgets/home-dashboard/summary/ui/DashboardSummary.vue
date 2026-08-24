<script setup lang="ts">
import { computed } from 'vue'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import type { DashboardSummaryDto } from '@/entities/dashboard'
import { resolveSummaryBanner, type SummaryBanner } from '../config'
import DashboardSummaryCard from './DashboardSummaryCard.vue'

const SKELETON_COUNT = 7

const props = defineProps<{ summary?: DashboardSummaryDto; isPending: boolean; isError: boolean }>()

const emit = defineEmits<{
    (e: 'retry'): void
}>()

/** The five task banners come from the response registry, in its order; the last two are fixed. */
const banners = computed<SummaryBanner[]>(() => {
    if (!props.summary) {
        return []
    }

    const taskBanners = props.summary.task_views.map((view) => ({
        ...resolveSummaryBanner(view.key),
        key: view.key,
        label: view.label,
        count: view.count,
        to: { name: 'tasks', query: { view: view.key } },
    }))

    return [
        ...taskBanners,
        {
            ...resolveSummaryBanner('projects'),
            key: 'projects',
            label: 'Projects',
            count: props.summary.projects_count,
            to: { name: 'projects' },
        },
        {
            ...resolveSummaryBanner('task_lists'),
            key: 'task_lists',
            label: 'Task Lists',
            count: props.summary.task_lists_count,
            to: { name: 'task-lists' },
        },
    ]
})
</script>

<template>
    <!--
        A failed row must say so rather than vanish: the cards are the page's headline numbers, and
        an empty strip where they were reads as "no tasks at all".
    -->
    <div
        v-if="isError"
        class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 gap-3 p-8 rounded-xl flex flex-col items-center border"
    >
        <p class="text-surface-500 text-sm">Could not load the dashboard summary.</p>
        <Button label="Try again" size="small" severity="secondary" @click="emit('retry')" />
    </div>

    <div v-else class="gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 grid grid-cols-2">
        <!-- Nothing is known about the row before the response arrives, so it holds its full width. -->
        <template v-if="isPending">
            <Skeleton v-for="n in SKELETON_COUNT" :key="n" height="5.25rem" border-radius="0.75rem" />
        </template>

        <template v-else>
            <DashboardSummaryCard v-for="banner in banners" :key="banner.key" :banner="banner" />
        </template>
    </div>
</template>
