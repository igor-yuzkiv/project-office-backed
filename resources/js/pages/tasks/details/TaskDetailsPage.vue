<script setup lang="ts">
import { computed, ref, toValue, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useLocalStorage } from '@vueuse/core'
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { PAGE_SIZE } from '@/app/config'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useBreadcrumbs } from '@/app/shell'
import { useTaskCommentsQuery, useTaskQuery } from '@/entities/task/queries'
import { useDeleteTaskMutation } from '@/entities/task/mutations'
import { useTaskAttachmentsDialog, useUpsertTaskComment } from '@/entities/task/composables'
import { uploadTaskAttachmentRequest } from '@/entities/task/api'
import { TaskAttachmentRoles } from '@/entities/task/config'
import { useDeleteCommentMutation } from '@/entities/comment'
import { useToast } from '@/shared/composables'
import { formatDate } from '@/shared/utils/date.util'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { IconButton } from '@/shared/components/button'
import { CopyableKey, PropertiesGrid } from '@/shared/components/display'
import { PageHead } from '@/shared/components/page-head'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { AttachmentsDialog } from '@/widgets/attachments/attachments-dialog'
import { CommentThread, type CommentKindFilter } from '@/widgets/comments/comment-thread'
import { TagList } from '@/widgets/tags/metadata'
import { TaskPriorityBars, TaskStatusTag } from '@/widgets/tasks/metadata'
import { TaskRail } from '@/widgets/tasks/task-rail'
import { UserAvatar } from '@/widgets/user/user-avatar'

const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
// A ref, not a value: stepping through a list with Previous / Next reuses this page.
const taskId = useRouteParams<string>('id')

const { task, isError } = useTaskQuery(taskId)
const { mutateWithConfirm: deleteTask } = useDeleteTaskMutation()
const attachmentsDialog = useTaskAttachmentsDialog(taskId)

const commentsPage = ref(1)
const commentsPagination = computed(() => ({ page: commentsPage.value, per_page: PAGE_SIZE }))
const {
    comments,
    paginationMeta: commentsMeta,
    isPending: isCommentsPending,
} = useTaskCommentsQuery(taskId, commentsPagination)
const { upsert: upsertComment } = useUpsertTaskComment(taskId)
const { mutateWithConfirm: deleteComment } = useDeleteCommentMutation()

const showAttachmentsDialog = ref(false)
const moreMenu = ref<InstanceType<typeof Menu>>()
const commentsKind = ref<CommentKindFilter>('all')
const railCollapsed = useLocalStorage('app:task:rail-collapsed', false)
const descriptionCollapsed = useLocalStorage('app:task:description-collapsed', false)
const commentsCollapsed = useLocalStorage('app:task:comments-collapsed', false)

const showRail = computed(() => Boolean(task.value?.task_list_id) && !railCollapsed.value)

const attachmentsLabel = computed(() =>
    attachmentsDialog.count.value === 1 ? '1 file' : `${attachmentsDialog.count.value} files`
)

const moreMenuItems = computed<MenuItem[]>(() => [{ label: 'Delete', icon: 'pi pi-trash', command: handleDeleteTask }])

function openEditor() {
    router.push({ name: 'task-edit', params: { id: taskId.value } })
}

function handleDeleteTask() {
    deleteTask(taskId.value, `Are you sure you want to delete "${task.value?.name}"?`, () =>
        router.push({ name: 'tasks' })
    )
}

function openMoreMenu(event: MouseEvent) {
    moreMenu.value?.toggle(event)
}

function handleCreateComment(content: string) {
    upsertComment({ mode: 'create', content })
}

function handleUpdateComment(value: { commentId: string; content: string }) {
    upsertComment({ ...value, mode: 'edit' })
}

async function handleCommentImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) => uploadTaskAttachmentRequest(toValue(taskId), file, TaskAttachmentRoles.COMMENTS))
    )
    callback(results.map((res) => res.data.url))
}

watch(isError, (value) => {
    if (value) toast.error('Failed to load task.')
})

watch(taskId, () => {
    commentsPage.value = 1
})

watch(
    task,
    (t) => {
        if (t) layoutStore.setPageTitle(`${t.key} | ${t.name}`)
    },
    { immediate: true }
)

useBreadcrumbs(() => [
    { label: 'Tasks', to: { name: 'tasks' } },
    ...(task.value?.project
        ? [{ label: task.value.project.name, to: { name: 'project-details', params: { id: task.value.project_id } } }]
        : []),
    ...(task.value?.task_list
        ? [
              {
                  label: task.value.task_list.name,
                  to: { name: 'task-list-details', params: { id: task.value.task_list.id } },
              },
          ]
        : []),
    { label: task.value ? task.value.key : 'Task' },
])
</script>

<template>
    <div
        v-if="task"
        class="min-h-0 grid flex-1"
        :class="showRail ? 'grid-cols-[minmax(0,1fr)_340px]' : 'grid-cols-[minmax(0,1fr)]'"
    >
        <div class="min-h-0 overflow-auto">
            <article class="page-container">
                <PageHead :title="task.name" mode="document">
                    <template #key>
                        <CopyableKey :value="task.key" size="md" />
                    </template>
                    <template v-if="task.updated_by" #meta>
                        Edited {{ formatRelativeTime(task.updated_at) }} by {{ task.updated_by.name }}
                    </template>
                    <template #actions>
                        <Button
                            label="Edit"
                            icon="pi pi-pencil"
                            size="small"
                            severity="secondary"
                            outlined
                            @click="openEditor"
                        />
                        <IconButton icon="pepicons-pop:dots-x" aria-label="More" @click="openMoreMenu" />
                        <IconButton
                            v-if="task.task_list_id && railCollapsed"
                            icon="tabler:layout-sidebar-right-expand"
                            aria-label="Show list"
                            title="Show list"
                            @click="railCollapsed = false"
                        />
                    </template>
                </PageHead>

                <PropertiesGrid class="mt-4" more-label="Dates, tags, attachments">
                    <span>Status</span>
                    <div><TaskStatusTag :status="task.status" /></div>

                    <span>Priority</span>
                    <div><TaskPriorityBars :priority="task.priority" /></div>

                    <span>Task list</span>
                    <div>
                        <RouterLink
                            v-if="task.task_list"
                            :to="{ name: 'task-list-details', params: { id: task.task_list.id } }"
                            class="text-accent text-[13px] hover:underline"
                        >
                            {{ task.task_list.key }} · {{ task.task_list.name }}
                        </RouterLink>
                        <span v-else class="text-ink-2 text-[13px]">—</span>
                    </div>

                    <span>Created</span>
                    <div>
                        <UserAvatar
                            v-if="task.created_by"
                            :initials="task.created_by.initials"
                            :avatar-url="task.created_by.avatar_url"
                            size="xsmall"
                        />
                        <span class="text-ink-2 text-[13px]">
                            {{ task.created_by?.name ?? 'Unknown' }}
                            <span class="text-ink-3">· {{ formatDate(task.created_at, 'MMM d') }}</span>
                        </span>
                    </div>

                    <span>Updated</span>
                    <div>
                        <UserAvatar
                            v-if="task.updated_by"
                            :initials="task.updated_by.initials"
                            :avatar-url="task.updated_by.avatar_url"
                            size="xsmall"
                        />
                        <span class="text-ink-2 text-[13px]">
                            {{ task.updated_by?.name ?? 'Unknown' }}
                            <span class="text-ink-3">· {{ formatRelativeTime(task.updated_at) }}</span>
                        </span>
                    </div>

                    <template #more>
                        <span>Dates</span>
                        <div class="text-ink-2 text-[13px]">
                            <span>{{ formatDate(task.start_date, 'MMM d') ?? '—' }}</span>
                            <span class="text-ink-3">→</span>
                            <span>{{ formatDate(task.due_date, 'MMM d') ?? '—' }}</span>
                        </div>

                        <span>Tags</span>
                        <div>
                            <TagList v-if="task.tags?.length" :tags="task.tags" inline />
                            <span v-else class="type-meta-3">No tags</span>
                        </div>

                        <span>Attachments</span>
                        <div>
                            <button
                                type="button"
                                class="text-accent cursor-pointer text-[13px] hover:underline"
                                @click="showAttachmentsDialog = true"
                            >
                                {{ attachmentsLabel }}
                            </button>
                        </div>
                    </template>
                </PropertiesGrid>

                <hr class="border-line mt-5 mb-7" />

                <h2 class="type-section mb-2.5 flex items-baseline">
                    <button
                        type="button"
                        class="gap-1.5 hover:text-ink-2 inline-flex cursor-pointer items-center"
                        :aria-expanded="!descriptionCollapsed"
                        aria-controls="task-description"
                        @click="descriptionCollapsed = !descriptionCollapsed"
                    >
                        <Icon
                            :icon="descriptionCollapsed ? 'heroicons:chevron-right' : 'heroicons:chevron-down'"
                            class="text-ink-3 text-[12px]"
                        />
                        Description
                    </button>
                </h2>
                <div v-show="!descriptionCollapsed" id="task-description">
                    <MarkdownPreview v-if="task.description" :model-value="task.description" class="type-prose" />
                    <p v-else class="type-meta-3">No description yet.</p>
                </div>

                <h2 class="type-section gap-2 mt-12 mb-2 flex items-baseline">
                    <button
                        type="button"
                        class="gap-1.5 hover:text-ink-2 inline-flex cursor-pointer items-center"
                        :aria-expanded="!commentsCollapsed"
                        aria-controls="task-comments"
                        @click="commentsCollapsed = !commentsCollapsed"
                    >
                        <Icon
                            :icon="commentsCollapsed ? 'heroicons:chevron-right' : 'heroicons:chevron-down'"
                            class="text-ink-3 text-[12px]"
                        />
                        Comments
                    </button>
                    <span v-if="commentsMeta" class="type-meta-3">{{ commentsMeta.total }}</span>
                </h2>
                <CommentThread
                    v-show="!commentsCollapsed"
                    id="task-comments"
                    v-model:page="commentsPage"
                    v-model:kind="commentsKind"
                    :comments="comments"
                    :pagination-meta="commentsMeta"
                    :is-pending="isCommentsPending"
                    :handle-image-upload="handleCommentImageUpload"
                    composer-placement="bottom"
                    class="-mx-4"
                    @create="handleCreateComment"
                    @update="handleUpdateComment"
                    @delete="deleteComment"
                />
            </article>
        </div>

        <TaskRail
            v-if="showRail && task.task_list_id"
            :task-list-id="task.task_list_id"
            :current-task-id="taskId"
            @hide="railCollapsed = true"
        />

        <Menu ref="moreMenu" :model="moreMenuItems" popup />

        <AttachmentsDialog
            v-model:visible="showAttachmentsDialog"
            :attachments="attachmentsDialog.attachments.value"
            :is-pending="attachmentsDialog.isPending.value"
            :is-uploading="attachmentsDialog.isUploading.value"
            :upload="attachmentsDialog.upload"
            :subtitle="task.key"
            @download="attachmentsDialog.download"
            @delete="attachmentsDialog.remove"
        />
    </div>
</template>
