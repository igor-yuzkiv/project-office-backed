<script setup lang="ts">
import { onScopeDispose } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useQueryClient } from '@tanstack/vue-query'
import { Icon } from '@iconify/vue'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { usePinnedProjectsQuery } from '@/entities/project/queries'
import { ProjectQueryKey } from '@/entities/project/config'
import { projectTaskCounts } from '@/entities/project/lib'

defineProps<{
    collapsed: boolean
}>()

const route = useRoute()
const queryClient = useQueryClient()

const { projects } = usePinnedProjectsQuery()

// The open-task counts change through task mutations, which invalidate their own slice only.
// The sidebar is always mounted, so it refreshes itself after any successful mutation.
const unsubscribe = queryClient.getMutationCache().subscribe((event) => {
    if (event.type === 'updated' && event.action.type === 'success') {
        queryClient.invalidateQueries({ queryKey: ProjectQueryKey.pinned })
    }
})
onScopeDispose(unsubscribe)

// The same Open figure as the project card, so the sidebar and the card never disagree.
function openCount(project: ProjectOverviewDto): number {
    return projectTaskCounts(project.task_status_counts).open
}

function isActive(project: ProjectOverviewDto): boolean {
    const routeProjectId = route.params.projectId ?? route.params.id
    return routeProjectId === project.id
}
</script>

<template>
    <section v-if="projects.length" :class="collapsed ? 'pt-2.5' : 'gap-0.5 flex flex-col'">
        <div
            v-if="!collapsed"
            class="text-ink-3 px-2 pt-3.5 pb-1 text-xs font-medium flex items-center justify-between"
        >
            <span>Pinned projects</span>
            <RouterLink
                v-tooltip.right="'Manage pinned'"
                :to="{ name: 'projects' }"
                class="hover:bg-hover hover:text-ink rounded p-0.5 transition-colors"
                aria-label="Manage pinned"
            >
                <Icon icon="tabler:pin" class="h-3.5 w-3.5" />
            </RouterLink>
        </div>

        <RouterLink
            v-for="project in projects"
            :key="project.id"
            v-tooltip.right="{ value: project.name, disabled: !collapsed }"
            :to="{ name: 'project-details', params: { id: project.id } }"
            class="hover:bg-hover hover:text-ink flex min-h-[26px] items-center rounded-[5px] text-[13px] transition-colors"
            :class="[
                isActive(project) ? 'bg-hover font-medium' : '',
                collapsed ? 'p-1.5 justify-center' : 'text-ink gap-2 px-2 py-1',
            ]"
            :aria-label="collapsed ? project.name : undefined"
        >
            <span
                class="bg-code-bg text-ink-2 h-5 w-5 font-semibold grid shrink-0 place-items-center rounded-[5px] text-[10.5px] tracking-[0.02em]"
            >
                {{ project.prefix }}
            </span>
            <template v-if="!collapsed">
                <span class="min-w-0 flex-1 truncate">{{ project.name }}</span>
                <span v-if="openCount(project)" class="text-ink-3 ml-auto text-[11.5px]">
                    {{ openCount(project) }}
                </span>
            </template>
        </RouterLink>
    </section>
</template>
