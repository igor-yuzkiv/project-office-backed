<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectQuery } from '@/entities/project/queries'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { ProjectIcon } from '@/widgets/projects/project-icon'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const projectId = route.params.id as string

const { project, isError } = useProjectQuery(projectId)

const activeTab = computed(
    () =>
        String(route.name ?? '')
            .split('.')
            .at(-1) ?? 'details'
)

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

function onTabChange(value: string | number) {
    router.push({ name: `project-details.${value}`, params: { id: projectId } })
}

useHeaderActions([])

useBreadcrumbs(() => [{ label: 'Projects', to: { name: 'projects' } }, { label: project.value?.name ?? 'Project' }])
</script>

<template>
    <div v-if="project" class="p-2 flex flex-1 overflow-hidden">
        <Tabs :value="activeTab" class="app-card flex flex-1 flex-col overflow-hidden" @update:value="onTabChange">
            <div class="p-3 flex shrink-0 items-start justify-between">
                <div class="gap-1 flex flex-col">
                    <div class="gap-x-2 text-2xl font-semibold flex items-center">
                        <ProjectIcon :prefix="project.prefix" size="small" />
                        <h1 class="text-surface-900">{{ project.name }}</h1>
                    </div>
                </div>
            </div>

            <TabList>
                <Tab value="details" class="px-4 py-2">Details</Tab>
                <Tab value="task-lists" class="px-4 py-2">Task Lists</Tab>
                <Tab value="tasks" class="px-4 py-2">Tasks</Tab>
                <Tab value="issues" class="px-4 py-2">Issues</Tab>
                <Tab value="attachments" class="px-4 py-2">Attachments</Tab>
                <Tab value="documentation" class="px-4 py-2">Documentation</Tab>
            </TabList>

            <div class="min-h-0 flex-1 overflow-auto">
                <router-view v-slot="{ Component }">
                    <transition name="page" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </Tabs>
    </div>
</template>
