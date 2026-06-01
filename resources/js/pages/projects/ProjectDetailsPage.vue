<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useProjectQuery } from '@/entities/project/queries'
import { DisplayDate, DisplayField } from '@/shared/components/display'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabPanel from 'primevue/tabpanel'
import TabPanels from 'primevue/tabpanels'
import Tabs from 'primevue/tabs'

const route = useRoute()
const projectId = route.params.id as string

const { project, isPending, isError } = useProjectQuery(projectId)
</script>

<template>
    <div class="p-6 gap-6 flex flex-col">
        <div v-if="isPending" class="text-surface-500">Loading...</div>
        <div v-else-if="isError" class="text-red-500">Failed to load project.</div>

        <template v-else-if="project">
            <div class="gap-4 flex items-center">
                <div
                    class="w-12 h-12 rounded-md bg-blue-500 text-white font-bold text-sm flex shrink-0 items-center justify-center select-none"
                >
                    {{ project.prefix.slice(0, 2).toUpperCase() }}
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-semibold text-surface-900">{{ project.name }}</h1>
                    <span class="text-surface-400 text-sm">{{ project.prefix }}</span>
                </div>
            </div>

            <div class="md:grid-cols-3 gap-4 p-4 rounded-lg border-surface-200 bg-surface-50 grid grid-cols-2 border">
                <DisplayField label="Name" :value="project.name" />
                <DisplayField label="Prefix" :value="project.prefix" />
                <DisplayField label="Created By" :value="project.created_by?.name" />
                <DisplayField label="Updated By" :value="project.updated_by?.name" />
                <DisplayDate label="Created At" :date="project.created_at" />
                <DisplayDate label="Updated At" :date="project.updated_at" />
            </div>

            <Tabs value="task-lists">
                <TabList>
                    <Tab value="task-lists">Task Lists</Tab>
                    <Tab value="tasks">Tasks</Tab>
                    <Tab value="issues">Issues</Tab>
                    <Tab value="attachments">Attachments</Tab>
                    <Tab value="documentation">Documentation</Tab>
                </TabList>
                <TabPanels>
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
        </template>
    </div>
</template>
