<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Panel from 'primevue/panel'
import { useProjectQuery } from '@/entities/project/queries'
import { DisplayFields } from '@/shared/components/display'
import type { DisplayFieldConfig } from '@/shared/components/display'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { formatDate, formatDateTime } from '@/shared/utils/date.util'
import type { IProject } from '@/entities/project/types'

const route = useRoute()
const projectId = route.params.id as string

const { project } = useProjectQuery(projectId)

const generalFields: DisplayFieldConfig<IProject>[] = [
    { name: 'name', label: 'Name' },
    { name: 'prefix', label: 'Prefix' },
    { name: 'status', label: 'Status' },
    { name: 'tags', label: 'Tags' },
]

const dateFields: DisplayFieldConfig<IProject>[] = [
    { name: 'start_date', label: 'Start Date', value: (p) => formatDate(p.start_date) },
    { name: 'end_date', label: 'End Date', value: (p) => formatDate(p.end_date) },
]

const systemFields = computed<DisplayFieldConfig<IProject>[]>(() => {
    const fields: DisplayFieldConfig<IProject>[] = [
        { name: 'created_by', label: 'Created By' },
        { name: 'created_at', label: 'Created At', value: (p) => formatDateTime(p.created_at) },
        { name: 'updated_by', label: 'Updated By' },
        { name: 'updated_at', label: 'Updated At', value: (p) => formatDateTime(p.updated_at) },
    ]
    if (project.value?.archived_at) {
        fields.push({ name: 'archived_at', label: 'Archived At', value: (p) => formatDateTime(p.archived_at) })
    }
    if (project.value?.archived_by) {
        fields.push({ name: 'archived_by', label: 'Archived By' })
    }
    return fields
})
</script>

<template>
    <div v-if="project" class="gap-4 p-2 flex flex-col">
        <Panel header="General" :toggleable="true">
            <DisplayFields :item="project" :fields="generalFields">
                <template #[`field:status:value`]="{ item }">
                    <ProjectStatusTag :status="item.status" class="w-fit" />
                </template>
                <template #[`field:tags:value`]="{ item }">
                    <TagList :tags="item.tags ?? []" />
                </template>
            </DisplayFields>
        </Panel>

        <Panel header="Dates" :toggleable="true">
            <DisplayFields :item="project" :fields="dateFields" />
        </Panel>

        <Panel header="System" :toggleable="true">
            <DisplayFields :item="project" :fields="systemFields">
                <template #[`field:created_by:value`]="{ item }">
                    <div v-if="item.created_by" class="gap-2 flex items-center">
                        <UserAvatar
                            :initials="item.created_by.initials"
                            :avatar-url="item.created_by.avatar_url"
                            size="small"
                        />
                        <span class="text-surface-700 dark:text-surface-300">{{ item.created_by.name }}</span>
                    </div>
                </template>
                <template #[`field:updated_by:value`]="{ item }">
                    <div v-if="item.updated_by" class="gap-2 flex items-center">
                        <UserAvatar
                            :initials="item.updated_by.initials"
                            :avatar-url="item.updated_by.avatar_url"
                            size="small"
                        />
                        <span class="text-surface-700 dark:text-surface-300">{{ item.updated_by.name }}</span>
                    </div>
                </template>
                <template #[`field:archived_by:value`]="{ item }">
                    <div v-if="item.archived_by" class="gap-2 flex items-center">
                        <UserAvatar
                            :initials="item.archived_by.initials"
                            :avatar-url="item.archived_by.avatar_url"
                            size="small"
                        />
                        <span class="text-surface-700 dark:text-surface-300">{{ item.archived_by.name }}</span>
                    </div>
                </template>
            </DisplayFields>
        </Panel>

        <Panel header="Description" :toggleable="true">
            <MarkdownPreview v-if="project.description" :model-value="project.description" />
            <p v-else class="text-sm text-surface-400 italic">No description available.</p>
        </Panel>
    </div>
</template>
