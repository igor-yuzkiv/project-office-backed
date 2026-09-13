<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useBreadcrumbs } from '@/app/shell'
import { useProjectQuery } from '@/entities/project/queries'
import { useDeleteProjectMutation, usePinProjectMutation, useUnpinProjectMutation } from '@/entities/project/mutations'
import { useToast } from '@/shared/composables'
import { IconButton } from '@/shared/components/button'
import { CopyableKey } from '@/shared/components/display'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const projectId = route.params.id as string

const { project, isError } = useProjectQuery(projectId)
const { mutateWithConfirm: deleteProject } = useDeleteProjectMutation()
const pinMutation = usePinProjectMutation()
const unpinMutation = useUnpinProjectMutation()

const moreMenu = ref<InstanceType<typeof Menu>>()

const activeTab = computed(
    () =>
        String(route.name ?? '')
            .split('.')
            .at(-1) ?? 'overview'
)

const tabs = computed(() => [
    { value: 'overview', label: 'Overview' },
    { value: 'task-lists', label: 'Task lists', count: project.value?.task_lists_count },
    { value: 'tasks', label: 'Tasks', count: project.value?.tasks_count },
    { value: 'activity', label: 'Activity' },
])

const moreMenuItems = computed<MenuItem[]>(() => [
    project.value?.is_pinned
        ? { label: 'Unpin', icon: 'pi pi-bookmark-fill', command: () => unpinMutation.mutate(projectId) }
        : { label: 'Pin', icon: 'pi pi-bookmark', command: () => pinMutation.mutate(projectId) },
    { separator: true },
    { label: 'Delete', icon: 'pi pi-trash', command: handleDeleteProject },
])

function onTabChange(value: string | number) {
    router.push({ name: `project-details.${value}`, params: { id: projectId } })
}

function openEditor() {
    router.push({ name: 'project-edit', params: { id: projectId } })
}

function openMoreMenu(event: MouseEvent) {
    moreMenu.value?.toggle(event)
}

function handleDeleteProject() {
    deleteProject(projectId, `Are you sure you want to delete "${project.value?.name}"?`, () =>
        router.push({ name: 'projects' })
    )
}

watch(isError, (error) => {
    if (error) toast.error('Failed to load project.')
})

watch(
    project,
    (p) => {
        if (p) layoutStore.setPageTitle(`${p.prefix} | ${p.name}`)
    },
    { immediate: true }
)

useBreadcrumbs(() => [{ label: 'Projects', to: { name: 'projects' } }, { label: project.value?.name ?? 'Project' }])
</script>

<template>
    <div v-if="project" class="min-h-0 flex-1 overflow-auto">
        <div class="page-container flex flex-col">
            <div class="gap-3.5 flex items-center">
                <ProjectIcon :prefix="project.prefix" :icon="project.icon" size="large" class="shrink-0" />

                <div class="min-w-0">
                    <h1 class="type-title truncate">{{ project.name }}</h1>
                    <div class="gap-2 mt-0.5 flex items-center">
                        <CopyableKey :value="project.prefix" size="md" />
                        <span class="text-ink-3">·</span>
                        <ProjectStatusTag :status="project.status" />
                    </div>
                </div>

                <div class="gap-1 ml-auto flex shrink-0 items-center">
                    <Button
                        label="Documentation"
                        size="small"
                        severity="secondary"
                        outlined
                        as="router-link"
                        :to="{ name: 'project-documentation', params: { projectId } }"
                    >
                        <template #icon>
                            <Icon icon="heroicons:book-open" class="mr-1 text-base" />
                        </template>
                    </Button>
                    <Button
                        label="Edit"
                        icon="pi pi-pencil"
                        size="small"
                        severity="secondary"
                        outlined
                        @click="openEditor"
                    />
                    <IconButton icon="pepicons-pop:dots-x" aria-label="More" @click="openMoreMenu" />
                </div>
            </div>

            <Tabs :value="activeTab" class="mt-5" @update:value="onTabChange">
                <TabList>
                    <Tab v-for="tab in tabs" :key="tab.value" :value="tab.value" class="px-2.5 py-2">
                        {{ tab.label }}
                        <span v-if="tab.count !== undefined" class="type-meta-3 ml-1 tabular-nums">
                            {{ tab.count }}
                        </span>
                    </Tab>
                </TabList>
            </Tabs>

            <div class="mt-6 min-h-0 flex flex-1 flex-col">
                <router-view v-slot="{ Component }">
                    <transition name="page" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </div>

        <Menu ref="moreMenu" :model="moreMenuItems" popup />
    </div>
</template>
