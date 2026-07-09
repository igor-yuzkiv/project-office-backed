<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import Panel from 'primevue/panel'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { DisplayFields } from '@/shared/components/display'
import type { DisplayFieldConfig } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { formatDateTime } from '@/shared/utils/date.util'
import type { IProjectDocument } from '@/entities/project-document/types'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)

const generalFields: DisplayFieldConfig<IProjectDocument>[] = [
    { name: 'key', label: 'Key' },
    { name: 'status', label: 'Status' },
    { name: 'project', label: 'Project' },
    { name: 'tags', label: 'Tags' },
]

const systemFields: DisplayFieldConfig<IProjectDocument>[] = [
    { name: 'created_by', label: 'Created By' },
    { name: 'created_at', label: 'Created At', value: (d) => formatDateTime(d.created_at) },
    { name: 'updated_by', label: 'Updated By' },
    { name: 'updated_at', label: 'Updated At', value: (d) => formatDateTime(d.updated_at) },
]
</script>

<template>
    <div v-if="projectDocument" class="gap-4 p-2 flex flex-col">
        <Panel header="General" :toggleable="true">
            <DisplayFields :item="projectDocument" :fields="generalFields">
                <template #[`field:status:value`]="{ item }">
                    <ProjectDocumentStatusTag :status="item.status" class="w-fit" />
                </template>
                <template #[`field:project:value`]="{ item }">
                    <RouterLink
                        v-if="item.project"
                        :to="{ name: 'project-details', params: { id: item.project_id } }"
                        class="app-link"
                    >
                        {{ item.project.prefix }} - {{ item.project.name }}
                    </RouterLink>
                </template>
                <template #[`field:tags:value`]="{ item }">
                    <TagList :tags="item.tags ?? []" />
                </template>
            </DisplayFields>
        </Panel>

        <Panel header="System" :toggleable="true">
            <DisplayFields :item="projectDocument" :fields="systemFields">
                <template #[`field:created_by:value`]="{ item }">
                    <div v-if="item.created_by" class="gap-2 flex items-center">
                        <UserAvatar :user-name="item.created_by.name" size="small" />
                        <span class="text-surface-700 dark:text-surface-300">{{ item.created_by.name }}</span>
                    </div>
                </template>
                <template #[`field:updated_by:value`]="{ item }">
                    <div v-if="item.updated_by" class="gap-2 flex items-center">
                        <UserAvatar :user-name="item.updated_by.name" size="small" />
                        <span class="text-surface-700 dark:text-surface-300">{{ item.updated_by.name }}</span>
                    </div>
                </template>
            </DisplayFields>
        </Panel>
    </div>
</template>
