<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import { Icon } from '@iconify/vue'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { PROJECT_COUNT_VIEWS, projectTaskCounts } from '@/entities/project/lib'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { IconButton } from '@/shared/components/button'
import { STATUS_COLORS, useStatusColors } from '@/shared/components/status-pill'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { useProjectCardMenu } from '../composables/use.project-card-menu'

const props = defineProps<{
    project: ProjectOverviewDto
}>()

const emit = defineEmits<{
    (e: 'edit', project: ProjectOverviewDto): void
    (e: 'delete', project: ProjectOverviewDto): void
}>()

const router = useRouter()

const menu = ref<InstanceType<typeof Menu>>()
const { items: menuItems } = useProjectCardMenu(() => props.project, {
    onEdit: (project) => emit('edit', project),
    onDelete: (project) => emit('delete', project),
})

const openColors = useStatusColors(STATUS_COLORS.open)
const progressColors = useStatusColors(STATUS_COLORS.progress)
const testColors = useStatusColors(STATUS_COLORS.test)

const counts = computed(() => projectTaskCounts(props.project.task_status_counts))

const tasksRoute = (view: string) => ({
    name: 'project-details.tasks',
    params: { id: props.project.id },
    query: { view },
})

const counters = computed(() => [
    { label: 'Open', count: counts.value.open, color: openColors.value.fg, to: tasksRoute(PROJECT_COUNT_VIEWS.open) },
    { label: 'In progress', count: counts.value.inProgress, color: progressColors.value.fg },
    { label: 'To test', count: counts.value.toTest, color: testColors.value.fg },
    { label: 'Tasks', count: counts.value.tasks, to: tasksRoute(PROJECT_COUNT_VIEWS.tasks) },
])

function openMenu(event: MouseEvent) {
    menu.value?.toggle(event)
}

// The whole card is the way in, but the name stays a real link so it can be opened in a new tab.
function openProject() {
    router.push({ name: 'project-details', params: { id: props.project.id } })
}
</script>

<template>
    <article
        class="app-card border-line hover:border-line-2 hover:bg-canvas gap-3 p-4 flex cursor-pointer flex-col border shadow-none transition-colors"
        @click="openProject"
    >
        <div class="gap-2.5 flex items-start">
            <ProjectIcon :prefix="project.prefix" :icon="project.icon" size="large" class="shrink-0" />

            <div class="gap-1.5 min-w-0 flex flex-1 flex-col">
                <RouterLink
                    :to="{ name: 'project-details', params: { id: project.id } }"
                    class="text-ink type-section truncate"
                    :title="project.name"
                    @click.stop
                >
                    {{ project.name }}
                </RouterLink>
                <ProjectStatusTag :status="project.status" class="w-fit" />
            </div>

            <IconButton icon="pepicons-pop:dots-x" aria-label="More" @click.stop="openMenu" />
        </div>

        <div v-if="project.last_activity" class="gap-1.5 type-meta min-w-0 flex items-center">
            <UserAvatar
                v-if="project.last_activity.actor"
                :initials="project.last_activity.actor.initials"
                :avatar-url="project.last_activity.actor.avatar_url"
                size="xsmall"
                class="shrink-0"
            />
            <span v-else class="bg-hover text-ink-3 h-5 w-5 grid shrink-0 place-items-center rounded-full">
                <Icon icon="tabler:bolt" class="text-[11px]" />
            </span>
            <span class="truncate" :title="project.last_activity.title">{{ project.last_activity.title }}</span>
            <time
                class="text-ink-3 ml-auto shrink-0"
                :datetime="project.last_activity.created_at"
                :title="project.last_activity.created_at"
            >
                {{ formatRelativeTime(project.last_activity.created_at) }}
            </time>
        </div>
        <div v-else-if="project.updated_by" class="gap-1.5 type-meta min-w-0 flex items-center">
            <UserAvatar
                :initials="project.updated_by.initials"
                :avatar-url="project.updated_by.avatar_url"
                size="xsmall"
                class="shrink-0"
            />
            <span class="truncate">Updated by {{ project.updated_by.name }}</span>
            <time class="text-ink-3 ml-auto shrink-0" :datetime="project.updated_at" :title="project.updated_at">
                {{ formatRelativeTime(project.updated_at) }}
            </time>
        </div>

        <div class="border-line gap-2 pt-3 mt-auto grid grid-cols-4 border-t">
            <component
                :is="counter.to ? RouterLink : 'span'"
                v-for="counter in counters"
                :key="counter.label"
                :to="counter.to"
                class="gap-2 min-w-0 flex items-center"
                :class="counter.to ? 'underline-offset-2 hover:underline' : ''"
                @click.stop
            >
                <i
                    class="h-3.5 w-3.5 shrink-0 rounded-full border-2"
                    :style="counter.color ? { borderColor: counter.color } : undefined"
                    :class="counter.color ? '' : 'border-ink-3'"
                />
                <span class="min-w-0">
                    <b class="text-ink text-sm font-semibold block tabular-nums">{{ counter.count }}</b>
                    <small class="type-meta whitespace-nowrap">{{ counter.label }}</small>
                </span>
            </component>
        </div>

        <Menu ref="menu" :model="menuItems" popup />
    </article>
</template>
