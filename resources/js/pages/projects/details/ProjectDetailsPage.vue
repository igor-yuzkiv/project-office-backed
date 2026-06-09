<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectQuery } from '@/entities/project/queries'
import { DisplayDate, DisplayField } from '@/shared/components/display'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabPanel from 'primevue/tabpanel'
import TabPanels from 'primevue/tabpanels'
import Tabs from 'primevue/tabs'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { UserAvatar } from '@/widgets/user/user-avatar'

const route = useRoute()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const projectId = route.params.id as string

const { project, isError } = useProjectQuery(projectId)

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

onMounted(() => {
    layoutStore.clearHeaderActions()
})

onUnmounted(() => {
    layoutStore.clearHeaderActions()
})
</script>

<template>
    <div v-if="project" class="p-2 flex flex-1 overflow-hidden">
        <Tabs value="details" class="app-card flex flex-1 flex-col overflow-hidden">
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

            <TabPanels class="min-h-0 flex-1 overflow-auto">
                <TabPanel value="details">
                    <div class="gap-4 grid grid-cols-2">
                        <DisplayField label="Name" :value="project.name" />
                        <DisplayField label="Prefix" :value="project.prefix" />
                        <DisplayField label="Created By">
                            <div v-if="project.created_by" class="gap-2 flex items-center">
                                <UserAvatar :user="project.created_by" size="small" />
                                <span class="text-surface-700">{{ project.created_by.name }}</span>
                            </div>
                        </DisplayField>
                        <DisplayField label="Updated By">
                            <div v-if="project.updated_by" class="gap-2 flex items-center">
                                <UserAvatar :user="project.updated_by" size="small" />
                                <span class="text-surface-700">{{ project.updated_by.name }}</span>
                            </div>
                        </DisplayField>
                        <DisplayDate label="Created At" :date="project.created_at" />
                        <DisplayDate label="Updated At" :date="project.updated_at" />
                    </div>
                </TabPanel>

                <TabPanel value="task-lists">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>

                <TabPanel value="tasks">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>

                <TabPanel value="issues">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>

                <TabPanel value="attachments">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>

                <TabPanel value="documentation">
                    <p class="text-surface-400">Not implemented</p>
                </TabPanel>
            </TabPanels>
        </Tabs>
    </div>
</template>
