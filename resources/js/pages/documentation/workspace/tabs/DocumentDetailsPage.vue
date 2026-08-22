<script setup lang="ts">
import { computed } from 'vue'
import Panel from 'primevue/panel'
import type { IProjectDocument } from '@/entities/project-document/types'
import { CopyToClipboard, DisplayFields } from '@/shared/components/display'
import type { DisplayFieldConfig } from '@/shared/components/display'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { formatDateTime } from '@/shared/utils/date.util'

const props = defineProps<{
    document: IProjectDocument
}>()

const generalFields: DisplayFieldConfig<IProjectDocument>[] = [
    { name: 'key', label: 'Key' },
    { name: 'status', label: 'Status' },
    { name: 'parent', label: 'Parent' },
    { name: 'tags', label: 'Tags' },
]

const systemFields: DisplayFieldConfig<IProjectDocument>[] = [
    { name: 'created_by', label: 'Created By' },
    { name: 'created_at', label: 'Created At', value: (document) => formatDateTime(document.created_at) },
    { name: 'updated_by', label: 'Updated By' },
    { name: 'updated_at', label: 'Updated At', value: (document) => formatDateTime(document.updated_at) },
]

// The document's path ends with the document itself, so its parent is the node before it.
const parent = computed(() => {
    const path = props.document.path ?? []

    return path.length > 1 ? path[path.length - 2] : undefined
})
</script>

<template>
    <div class="gap-4 p-2 flex flex-col">
        <Panel header="General" :toggleable="true">
            <DisplayFields :item="document" :fields="generalFields">
                <template #[`field:key:value`]="{ item }">
                    <CopyToClipboard :text="item.key" class="text-surface-700 dark:text-surface-200" />
                </template>
                <template #[`field:status:value`]="{ item }">
                    <ProjectDocumentStatusTag :status="item.status" variant="light" class="w-fit" />
                </template>
                <template #[`field:parent:value`]="{ item }">
                    <RouterLink
                        v-if="parent"
                        :to="{
                            name: 'project-documentation.document',
                            params: { projectId: item.project_id, documentId: parent.id },
                        }"
                        class="app-link truncate"
                    >
                        {{ parent.title }}
                    </RouterLink>
                    <span v-else class="text-surface-400 text-sm">Root document</span>
                </template>
                <template #[`field:tags:value`]="{ item }">
                    <TagList v-if="item.tags?.length" :tags="item.tags" />
                    <span v-else class="text-surface-400 text-sm">No tags yet.</span>
                </template>
            </DisplayFields>
        </Panel>

        <Panel header="System" :toggleable="true">
            <DisplayFields :item="document" :fields="systemFields">
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
            </DisplayFields>
        </Panel>
    </div>
</template>
