<script setup lang="ts">
import { computed } from 'vue'
import type { IProjectDocument } from '@/entities/project-document/types'
import { PropertiesGrid } from '@/shared/components/display'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'

const props = defineProps<{
    document: IProjectDocument
}>()

// The document's path ends with the document itself, so its parent is the node before it.
const parent = computed(() => {
    const path = props.document.path ?? []

    return path.length > 1 ? path[path.length - 2] : undefined
})
</script>

<template>
    <div class="min-h-0 overflow-auto">
        <div class="page-container">
            <PropertiesGrid>
                <span>Status</span>
                <div><ProjectDocumentStatusTag :status="document.status" /></div>

                <span>Parent</span>
                <div>
                    <RouterLink
                        v-if="parent"
                        :to="{
                            name: 'project-documentation.document',
                            params: { projectId: document.project_id, documentId: parent.id },
                        }"
                        class="text-accent text-[13px] hover:underline"
                    >
                        {{ parent.title }}
                    </RouterLink>
                    <span v-else class="text-ink-2 text-[13px]">Root document</span>
                </div>

                <span>Tags</span>
                <div>
                    <TagList v-if="document.tags?.length" :tags="document.tags" inline />
                    <span v-else class="text-ink-2 text-[13px]">—</span>
                </div>

                <span>Created</span>
                <div>
                    <UserAvatar
                        v-if="document.created_by"
                        :initials="document.created_by.initials"
                        :avatar-url="document.created_by.avatar_url"
                        size="xsmall"
                    />
                    <span class="text-ink-2 text-[13px]">
                        {{ document.created_by?.name ?? 'Unknown' }}
                        <span class="text-ink-3">· {{ formatRelativeTime(document.created_at) }}</span>
                    </span>
                </div>

                <span>Updated</span>
                <div>
                    <UserAvatar
                        v-if="document.updated_by"
                        :initials="document.updated_by.initials"
                        :avatar-url="document.updated_by.avatar_url"
                        size="xsmall"
                    />
                    <span class="text-ink-2 text-[13px]">
                        {{ document.updated_by?.name ?? 'Unknown' }}
                        <span class="text-ink-3">· {{ formatRelativeTime(document.updated_at) }}</span>
                    </span>
                </div>
            </PropertiesGrid>
        </div>
    </div>
</template>
