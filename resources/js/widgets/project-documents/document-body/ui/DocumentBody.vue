<script setup lang="ts">
import { computed, watch } from 'vue'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
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
    isDirty?: boolean
    isSaving?: boolean
    canAnnotate?: boolean
    handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void
}>()

const draftTitle = defineModel<string>('draftTitle', { default: '' })
const draftContent = defineModel<string>('draftContent', { default: '' })

const emit = defineEmits<{
    (e: 'open-document', documentId: string): void
    (e: 'edit'): void
    (e: 'annotate'): void
    (e: 'move'): void
    (e: 'delete'): void
    (e: 'save'): void
    (e: 'cancel'): void
}>()

const activeTab = defineModel<string>('tab', { default: 'document' })

const documentId = computed(() => props.document.id)

// Shares the tab's own query key, so the count costs no extra request. The API
// carries no task count for a document — it exists once the list has loaded.
const { paginationMeta: taskPaginationMeta } = useProjectDocumentTasksQuery(documentId, {
    page: 1,
    per_page: PAGE_SIZE,
})

const ancestors = computed(() => (props.document.path ?? []).slice(0, -1))

watch(
    () => props.isEditing,
    (editing) => {
        if (editing) activeTab.value = 'document'
    }
)
</script>

<template>
    <div class="flex h-full flex-col overflow-hidden">
        <div v-if="ancestors.length" class="gap-1 px-4 pt-3 text-xs text-surface-500 flex items-center truncate">
            <template v-for="(node, index) in ancestors" :key="node.id">
                <span v-if="index > 0" class="text-surface-400">/</span>
                <button type="button" class="app-link truncate" @click="emit('open-document', node.id)">
                    {{ node.title }}
                </button>
            </template>
        </div>

        <div class="gap-3 px-4 pt-2 flex items-start justify-between">
            <div class="gap-x-2 min-w-0 text-xl font-semibold flex flex-1 items-center">
                <CopyToClipboard class="text-surface-400 text-sm" :text="document.key" hide-copy-icon />
                <EditableDocumentTitle v-if="isEditing" v-model="draftTitle" class="min-w-0 flex-1" />
                <h1 v-else class="text-surface-900 dark:text-surface-0 truncate">{{ document.title }}</h1>
            </div>

            <div class="gap-1 flex shrink-0 items-center">
                <span v-if="isEditing && isDirty" class="mr-2 text-xs text-amber-600 dark:text-amber-400">
                    Unsaved changes
                </span>

                <template v-if="isEditing">
                    <Button
                        :label="isSaving ? 'Saving…' : 'Save'"
                        size="small"
                        text
                        :disabled="isSaving"
                        @click="emit('save')"
                    >
                        <template #icon><Icon icon="heroicons:check" class="mr-1 text-base" /></template>
                    </Button>
                    <Button label="Cancel" size="small" text severity="secondary" @click="emit('cancel')">
                        <template #icon><Icon icon="heroicons:x-mark" class="mr-1 text-base" /></template>
                    </Button>
                </template>

                <template v-else>
                    <Button label="Edit" size="small" text severity="secondary" @click="emit('edit')">
                        <template #icon><Icon icon="heroicons:pencil-square" class="mr-1 text-base" /></template>
                    </Button>
                    <Button
                        v-if="canAnnotate"
                        label="Annotate"
                        size="small"
                        text
                        severity="secondary"
                        @click="emit('annotate')"
                    >
                        <template #icon
                            ><Icon icon="heroicons:chat-bubble-left-right" class="mr-1 text-base"
                        /></template>
                    </Button>
                    <Button label="Move" size="small" text severity="secondary" @click="emit('move')">
                        <template #icon><Icon icon="heroicons:arrows-right-left" class="mr-1 text-base" /></template>
                    </Button>
                    <Button label="Delete" size="small" text severity="danger" @click="emit('delete')">
                        <template #icon><Icon icon="heroicons:trash" class="mr-1 text-base" /></template>
                    </Button>
                </template>
            </div>
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
