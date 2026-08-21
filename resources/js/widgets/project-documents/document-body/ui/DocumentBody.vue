<script setup lang="ts">
import { computed, watch } from 'vue'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useProjectDocumentTasksQuery } from '@/entities/project-document'
import type { IProjectDocument } from '@/entities/project-document/types'
import { CopyToClipboard } from '@/shared/components/display'
import { PAGE_SIZE } from '@/app/config'
import { MarkdownEditor } from '@/shared/components/md-editor'
import EditableDocumentTitle from './EditableDocumentTitle.vue'
import DocumentContentTab from './DocumentContentTab.vue'
import DocumentCommentsTab from './DocumentCommentsTab.vue'
import DocumentRelatedTasksTab from './DocumentRelatedTasksTab.vue'

const props = defineProps<{
    document: IProjectDocument
    isEditing?: boolean
    handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void
}>()

const draftTitle = defineModel<string>('draftTitle', { default: '' })
const draftContent = defineModel<string>('draftContent', { default: '' })

const activeTab = defineModel<string>('tab', { default: 'document' })

const documentId = computed(() => props.document.id)

// Shares the tab's own query key, so the count costs no extra request. The API
// carries no task count for a document — it exists once the list has loaded.
const { paginationMeta: taskPaginationMeta } = useProjectDocumentTasksQuery(documentId, {
    page: 1,
    per_page: PAGE_SIZE,
})

watch(
    () => props.isEditing,
    (editing) => {
        if (editing) activeTab.value = 'document'
    }
)
</script>

<template>
    <div class="flex h-full flex-col overflow-hidden">
        <div class="gap-x-2 px-4 pt-2 min-w-0 text-xl font-semibold flex items-center">
            <CopyToClipboard class="text-surface-400 text-sm" :text="document.key" hide-copy-icon />
            <EditableDocumentTitle v-if="isEditing" v-model="draftTitle" class="min-w-0 flex-1" />
            <h1 v-else class="text-surface-900 dark:text-surface-0 truncate">{{ document.title }}</h1>
        </div>

        <Tabs :value="activeTab" class="min-h-0 flex flex-1 flex-col" @update:value="activeTab = String($event)">
            <TabList>
                <Tab value="document" class="px-4 py-2">Document</Tab>
                <Tab value="comments" class="px-4 py-2" :disabled="isEditing">
                    Comments
                    <span v-if="document.comments_count" class="text-surface-400 ml-1 text-xs">
                        {{ document.comments_count }}
                    </span>
                </Tab>
                <Tab value="tasks" class="px-4 py-2" :disabled="isEditing">
                    Related tasks
                    <span v-if="taskPaginationMeta?.total" class="text-surface-400 ml-1 text-xs">
                        {{ taskPaginationMeta.total }}
                    </span>
                </Tab>
            </TabList>

            <div class="min-h-0 flex-1 overflow-auto">
                <MarkdownEditor
                    v-if="isEditing"
                    v-model="draftContent"
                    preview
                    style="height: 100%"
                    :handle-image-upload="handleImageUpload"
                />
                <DocumentContentTab v-else-if="activeTab === 'document'" :content="document.content" class="p-4" />
                <DocumentCommentsTab v-else-if="activeTab === 'comments'" :document-id="document.id" />
                <DocumentRelatedTasksTab v-else :document-id="document.id" :project-id="document.project_id" />
            </div>
        </Tabs>
    </div>
</template>
