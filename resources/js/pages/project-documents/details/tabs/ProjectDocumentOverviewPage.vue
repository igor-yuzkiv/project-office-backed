<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouteParams } from '@vueuse/router'
import Panel from 'primevue/panel'
import Paginator from 'primevue/paginator'
import { Icon } from '@iconify/vue'
import { useProjectDocumentQuery, useProjectDocumentChildrenQuery } from '@/entities/project-document'
import { DisplayFields } from '@/shared/components/display'
import type { DisplayFieldConfig } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { formatDateTime } from '@/shared/utils/date.util'
import type { IProjectDocument } from '@/entities/project-document/types'
import { PAGE_SIZE } from '@/app/config'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)

const page = ref(1)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))
const {
    children,
    paginationMeta,
    isPending: isChildrenPending,
} = useProjectDocumentChildrenQuery(() => projectDocument.value?.project_id ?? '', documentId, pagination)

const showPaginator = computed(() => paginationMeta.value && paginationMeta.value.last_page > 1)

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
}

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

        <Panel header="Child Documents" :toggleable="true">
            <div v-if="isChildrenPending" class="text-surface-400 text-sm">Loading child documents...</div>
            <div v-else-if="children.length === 0" class="text-surface-400 text-sm">No child documents yet.</div>
            <div v-else class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y">
                <RouterLink
                    v-for="child in children"
                    :key="child.id"
                    :to="{ name: 'project-document-details', params: { id: child.id } }"
                    class="gap-2 py-2 hover:bg-surface-50 dark:hover:bg-surface-800 flex items-center"
                >
                    <Icon :icon="child.has_children ? 'heroicons:folder' : 'heroicons:document-text'" class="text-lg" />
                    <span class="text-surface-500">{{ child.key }}</span>
                    <span class="app-link">{{ child.title }}</span>
                    <ProjectDocumentStatusTag :status="child.status" variant="light" class="ml-auto" />
                </RouterLink>
            </div>

            <Paginator
                v-if="showPaginator"
                :rows="PAGE_SIZE"
                :total-records="paginationMeta!.total"
                :first="(page - 1) * PAGE_SIZE"
                @page="onPageChange"
            />
        </Panel>
    </div>
</template>
