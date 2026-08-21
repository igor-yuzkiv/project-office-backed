<script setup lang="ts">
import { computed, ref } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Select from 'primevue/select'
import type { IProjectDocument, ProjectDocumentStatusValue } from '@/entities/project-document/types'
import type { ITag } from '@/entities/tag/types'
import { useProjectDocumentTasksQuery } from '@/entities/project-document'
import { projectDocumentStatusOptions } from '@/entities/project-document/config'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { IconButton } from '@/shared/components/button'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { TaskStatusTag } from '@/widgets/tasks/metadata'
import { TagList } from '@/widgets/tags/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'

const RELATED_TASKS_PREVIEW_COUNT = 3

const props = defineProps<{
    document?: IProjectDocument
    // Editing swaps two controls; everything else on the panel reads the same.
    isEditing?: boolean
}>()

const draftStatus = defineModel<ProjectDocumentStatusValue | undefined>('draftStatus')
const draftTags = defineModel<ITag[]>('draftTags', { default: () => [] })

const isOpen = defineModel<boolean>('open', { required: true })

const emit = defineEmits<{
    (e: 'open-document', documentId: string): void
    (e: 'view-all-tasks'): void
}>()

const documentId = computed(() => props.document?.id ?? '')

const { tasks, paginationMeta, isPending, isError } = useProjectDocumentTasksQuery(
    documentId,
    { page: 1, per_page: RELATED_TASKS_PREVIEW_COUNT },
    { enabled: () => Boolean(documentId.value) }
)

// The document's path ends with the document itself, so its parent is the node before it.
const parent = computed(() => {
    const path = props.document?.path ?? []

    return path.length > 1 ? path[path.length - 2] : undefined
})

const isTagsDialogVisible = ref(false)

const hiddenTaskCount = computed(() => Math.max((paginationMeta.value?.total ?? 0) - tasks.value.length, 0))
</script>

<template>
    <section class="flex h-full flex-col overflow-hidden">
        <header class="gap-2 py-2 flex items-center" :class="isOpen ? 'px-3 justify-between' : 'px-0 justify-center'">
            <h2
                v-if="isOpen"
                class="text-surface-600 dark:text-surface-300 text-xs font-semibold tracking-wide uppercase"
            >
                Document details
            </h2>

            <Button
                severity="secondary"
                text
                rounded
                class="shrink-0"
                :title="isOpen ? 'Hide details' : 'Show details'"
                :aria-label="isOpen ? 'Hide details' : 'Show details'"
                @click="isOpen = !isOpen"
            >
                <template #icon>
                    <Icon
                        :icon="isOpen ? 'heroicons:chevron-double-right' : 'heroicons:chevron-double-left'"
                        class="text-base"
                    />
                </template>
            </Button>
        </header>

        <div v-if="isOpen" class="gap-5 p-3 flex flex-1 flex-col overflow-auto">
            <p v-if="!document" class="text-surface-400 text-xs">Open a document to see its details.</p>

            <template v-else>
                <section class="gap-2 flex flex-col">
                    <h3 class="text-surface-400 text-xs font-semibold tracking-wide uppercase">General</h3>

                    <div class="gap-2 text-sm grid grid-cols-[auto_1fr] items-center">
                        <span class="text-surface-500 text-xs">Key</span>
                        <CopyToClipboard :text="document.key" class="text-surface-700 dark:text-surface-200" />

                        <span class="text-surface-500 text-xs">Status</span>
                        <Select
                            v-if="isEditing"
                            v-model="draftStatus"
                            :options="projectDocumentStatusOptions()"
                            option-label="label"
                            option-value="value"
                            size="small"
                            variant="filled"
                            class="hover:!border-surface-300 w-full !border-transparent !bg-transparent"
                        />
                        <ProjectDocumentStatusTag v-else :status="document.status" variant="light" class="w-fit" />

                        <span class="text-surface-500 text-xs">Parent</span>
                        <button
                            v-if="parent && !isEditing"
                            type="button"
                            class="app-link truncate text-left"
                            @click="emit('open-document', parent.id)"
                        >
                            {{ parent.title }}
                        </button>
                        <span v-else-if="parent" class="text-surface-700 dark:text-surface-200 truncate">
                            {{ parent.title }}
                        </span>
                        <span v-else class="text-surface-400 text-xs">Root document</span>
                    </div>
                </section>

                <section class="gap-2 flex flex-col">
                    <h3 class="text-surface-400 text-xs font-semibold tracking-wide uppercase">Tags</h3>

                    <div v-if="isEditing" class="gap-2 flex items-center">
                        <IconButton
                            size="small"
                            severity="success"
                            icon="mdi:tag-edit"
                            @click="isTagsDialogVisible = true"
                        />
                        <TagList :tags="draftTags" />
                    </div>

                    <TagList v-else-if="document.tags?.length" :tags="document.tags" />
                    <p v-else class="text-surface-400 text-xs">No tags yet.</p>
                </section>

                <section class="gap-2 flex flex-col">
                    <h3 class="text-surface-400 text-xs font-semibold tracking-wide uppercase">History</h3>

                    <div v-if="document.created_by" class="gap-2 flex items-center">
                        <UserAvatar
                            :initials="document.created_by.initials"
                            :avatar-url="document.created_by.avatar_url"
                            size="small"
                        />
                        <div class="min-w-0 flex flex-col">
                            <span class="text-surface-700 dark:text-surface-200 text-sm truncate">
                                {{ document.created_by.name }}
                            </span>
                            <span class="text-surface-400 text-xs">
                                Created <DisplayDate :date="document.created_at" />
                            </span>
                        </div>
                    </div>

                    <div v-if="document.updated_by" class="gap-2 flex items-center">
                        <UserAvatar
                            :initials="document.updated_by.initials"
                            :avatar-url="document.updated_by.avatar_url"
                            size="small"
                        />
                        <div class="min-w-0 flex flex-col">
                            <span class="text-surface-700 dark:text-surface-200 text-sm truncate">
                                {{ document.updated_by.name }}
                            </span>
                            <span class="text-surface-400 text-xs">
                                Updated <DisplayDate :date="document.updated_at" />
                            </span>
                        </div>
                    </div>

                    <p v-if="!document.created_by && !document.updated_by" class="text-surface-400 text-xs">
                        No history recorded.
                    </p>
                </section>

                <section class="gap-2 flex flex-col">
                    <h3 class="text-surface-400 text-xs font-semibold tracking-wide uppercase">Related tasks</h3>

                    <div v-if="isPending" class="gap-2 flex flex-col">
                        <Skeleton v-for="n in RELATED_TASKS_PREVIEW_COUNT" :key="n" height="1.5rem" />
                    </div>

                    <p v-else-if="isError" class="text-xs text-red-500">Could not load related tasks.</p>

                    <p v-else-if="!tasks.length" class="text-surface-400 text-xs">No tasks linked yet.</p>

                    <template v-else>
                        <RouterLink
                            v-for="task in tasks"
                            :key="task.id"
                            :to="{ name: 'task-details', params: { id: task.id } }"
                            class="hover:bg-surface-100 dark:hover:bg-surface-800 gap-2 px-1 py-1 rounded flex items-center"
                        >
                            <span class="text-surface-400 text-xs shrink-0">{{ task.key }}</span>
                            <span class="text-surface-700 dark:text-surface-200 min-w-0 text-sm flex-1 truncate">
                                {{ task.name }}
                            </span>
                            <TaskStatusTag :status="task.status" class="shrink-0" />
                        </RouterLink>

                        <Button
                            v-if="hiddenTaskCount > 0 && !isEditing"
                            :label="`View all (${paginationMeta?.total})`"
                            size="small"
                            severity="secondary"
                            text
                            class="w-fit"
                            @click="emit('view-all-tasks')"
                        />
                    </template>
                </section>
            </template>
        </div>

        <ManageRecordTagsDialog v-model:visible="isTagsDialogVisible" v-model="draftTags" />
    </section>
</template>
