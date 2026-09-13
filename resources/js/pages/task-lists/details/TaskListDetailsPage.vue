<script setup lang="ts">
import { computed, ref, toValue, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { PAGE_SIZE } from '@/app/config'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useBreadcrumbs } from '@/app/shell'
import { useTaskListCommentsQuery, useTaskListQuery, useTaskListTasksQuery } from '@/entities/task-list/queries'
import { useDeleteTaskListMutation } from '@/entities/task-list/mutations'
import { useTaskListAttachmentsDialog, useUpsertTaskListComment } from '@/entities/task-list/composables'
import { uploadTaskListAttachmentRequest } from '@/entities/task-list/api'
import { TaskListAttachmentRoles } from '@/entities/task-list/config'
import { taskListProgress } from '@/entities/task-list/lib'
import { useDeleteCommentMutation } from '@/entities/comment'
import { useToast } from '@/shared/composables'
import { formatDate } from '@/shared/utils/date.util'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { IconButton } from '@/shared/components/button'
import { CopyableKey, PropertiesGrid } from '@/shared/components/display'
import { PageHead } from '@/shared/components/page-head'
import { ProgressBar } from '@/shared/components/progress-bar'
import { STATUS_COLORS, useStatusColors } from '@/shared/components/status-pill'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { CollapsibleSection } from '@/shared/components/section'
import { AttachmentsDialog } from '@/widgets/attachments/attachments-dialog'
import { CommentThread } from '@/widgets/comments/comment-thread'
import { TagList } from '@/widgets/tags/metadata'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'
import { TaskListPlan } from '@/widgets/task-list/plan'
import { AddTasksToTaskListDialog, useAddTasksToTaskListDialog } from '@/widgets/task-list/add-tasks-dialog'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { UserAvatar } from '@/widgets/user/user-avatar'

const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
// A ref, not a value: moving between two lists reuses this page.
const taskListId = useRouteParams<string>('id')

const { taskList, isError } = useTaskListQuery(taskListId)
const { tasks, isPending: isTasksPending } = useTaskListTasksQuery(taskListId)
const { mutateWithConfirm: deleteTaskList } = useDeleteTaskListMutation()

const attachmentsDialog = useTaskListAttachmentsDialog(taskListId)
const addTasksDialog = useAddTasksToTaskListDialog(
    () => taskListId.value,
    () => taskList.value?.project_id
)
const taskCreateDialog = useTaskCreateDialog()

const commentsPage = ref(1)
const commentsPagination = computed(() => ({ page: commentsPage.value, per_page: PAGE_SIZE }))
const {
    comments,
    paginationMeta: commentsMeta,
    isPending: isCommentsPending,
} = useTaskListCommentsQuery(taskListId, commentsPagination)
const { upsert: upsertComment } = useUpsertTaskListComment(taskListId)
const { mutateWithConfirm: deleteComment } = useDeleteCommentMutation()

const showAttachmentsDialog = ref(false)
const moreMenu = ref<InstanceType<typeof Menu>>()

const doneColors = useStatusColors(STATUS_COLORS.done)
const inProgressColors = useStatusColors(STATUS_COLORS.progress)

const progress = computed(() => taskListProgress(taskList.value?.task_status_counts))
const progressSegments = computed(() => [
    { value: progress.value.done, colors: doneColors.value },
    { value: progress.value.inProgress, colors: inProgressColors.value },
])
const progressLabel = computed(() => {
    const { done, inProgress, total } = progress.value
    const parts = [`${done} of ${total} done`]
    if (inProgress > 0) parts.push(`${inProgress} in progress`)
    return parts.join(' · ')
})

const attachmentsLabel = computed(() =>
    attachmentsDialog.count.value === 1 ? '1 file' : `${attachmentsDialog.count.value} files`
)

const moreMenuItems = computed<MenuItem[]>(() => [
    { label: 'Delete', icon: 'pi pi-trash', command: handleDeleteTaskList },
])

function openTaskCreateDialog() {
    taskCreateDialog.open(taskList.value?.project, taskList.value)
}

function openEditor() {
    router.push({ name: 'task-list-edit', params: { id: taskListId.value } })
}

function handleDeleteTaskList() {
    deleteTaskList(taskListId.value, `Are you sure you want to delete "${taskList.value?.name}"?`, () =>
        router.push({ name: 'task-lists' })
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
        files.map((file) =>
            uploadTaskListAttachmentRequest(toValue(taskListId), file, TaskListAttachmentRoles.COMMENTS)
        )
    )
    callback(results.map((res) => res.data.url))
}

watch(isError, (value) => {
    if (value) toast.error('Failed to load task list.')
})

watch(
    taskList,
    (list) => {
        if (list) layoutStore.setPageTitle(`${list.key} | ${list.name}`)
    },
    { immediate: true }
)

useBreadcrumbs(() => [
    { label: 'Task lists', to: { name: 'task-lists' } },
    ...(taskList.value?.project
        ? [
              {
                  label: taskList.value.project.name,
                  to: { name: 'project-details', params: { id: taskList.value.project_id } },
              },
          ]
        : []),
    { label: taskList.value ? taskList.value.key : 'Task list' },
])
</script>

<template>
    <div v-if="taskList" class="min-h-0 flex-1 overflow-auto">
        <article class="page-container">
            <PageHead :title="taskList.name">
                <template #key>
                    <CopyableKey :value="taskList.key" size="md" />
                </template>
                <template v-if="taskList.updated_by" #meta>
                    Edited {{ formatRelativeTime(taskList.updated_at) }} by {{ taskList.updated_by.name }}
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
                </template>
            </PageHead>

            <PropertiesGrid class="mt-4" more-label="Tags, attachments">
                <span>Status</span>
                <div><TaskListStatusTag :status="taskList.status" /></div>

                <span>Progress</span>
                <div>
                    <ProgressBar :segments="progressSegments" :total="progress.total" class="w-40" />
                    <span class="text-ink-2 ml-2 text-[13px]">{{ progressLabel }}</span>
                </div>

                <span>Created</span>
                <div>
                    <UserAvatar
                        v-if="taskList.created_by"
                        :initials="taskList.created_by.initials"
                        :avatar-url="taskList.created_by.avatar_url"
                        size="xsmall"
                    />
                    <span class="text-ink-2 text-[13px]">
                        {{ taskList.created_by?.name ?? 'Unknown' }}
                        <span class="text-ink-3">· {{ formatDate(taskList.created_at, 'MMM d') }}</span>
                    </span>
                </div>

                <span>Updated</span>
                <div>
                    <UserAvatar
                        v-if="taskList.updated_by"
                        :initials="taskList.updated_by.initials"
                        :avatar-url="taskList.updated_by.avatar_url"
                        size="xsmall"
                    />
                    <span class="text-ink-2 text-[13px]">
                        {{ taskList.updated_by?.name ?? 'Unknown' }}
                        <span class="text-ink-3">· {{ formatRelativeTime(taskList.updated_at) }}</span>
                    </span>
                </div>

                <template #more>
                    <span>Tags</span>
                    <div>
                        <TagList v-if="taskList.tags?.length" :tags="taskList.tags" inline />
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

            <CollapsibleSection title="Plan" storage-key="app:task-list:plan-collapsed">
                <template #aside>
                    <span class="type-meta-3">{{ tasks.length }} {{ tasks.length === 1 ? 'task' : 'tasks' }}</span>
                </template>
                <TaskListPlan
                    :tasks="tasks"
                    :is-pending="isTasksPending"
                    @add="addTasksDialog.open"
                    @create="openTaskCreateDialog"
                />
            </CollapsibleSection>

            <CollapsibleSection
                title="Description"
                storage-key="app:task-list:description-collapsed"
                heading-class="mt-9 mb-2.5"
            >
                <MarkdownPreview v-if="taskList.description" :model-value="taskList.description" class="type-prose" />
                <p v-else class="type-meta-3">No description yet.</p>
            </CollapsibleSection>

            <h2 class="type-section gap-2 mt-12 mb-2 flex items-baseline">
                Comments
                <span v-if="commentsMeta" class="type-meta-3">{{ commentsMeta.total }}</span>
            </h2>
            <CommentThread
                v-model:page="commentsPage"
                :comments="comments"
                :pagination-meta="commentsMeta"
                :is-pending="isCommentsPending"
                :handle-image-upload="handleCommentImageUpload"
                class="-mx-4"
                @create="handleCreateComment"
                @update="handleUpdateComment"
                @delete="deleteComment"
            />
        </article>

        <Menu ref="moreMenu" :model="moreMenuItems" popup />

        <AttachmentsDialog
            v-model:visible="showAttachmentsDialog"
            :attachments="attachmentsDialog.attachments.value"
            :is-pending="attachmentsDialog.isPending.value"
            :is-uploading="attachmentsDialog.isUploading.value"
            :upload="attachmentsDialog.upload"
            :subtitle="taskList.key"
            @download="attachmentsDialog.download"
            @delete="attachmentsDialog.remove"
        />

        <AddTasksToTaskListDialog
            v-model:visible="addTasksDialog.visible.value"
            v-model:selected="addTasksDialog.selected.value"
            v-model:search-query="addTasksDialog.searchQuery.value"
            :candidates="addTasksDialog.candidates.value"
            :pagination-meta="addTasksDialog.paginationMeta.value"
            :page="addTasksDialog.page.value"
            :is-pending="addTasksDialog.isPending.value"
            :is-saving="addTasksDialog.isSaving.value"
            :can-save="addTasksDialog.canSave.value"
            @page-change="addTasksDialog.onPageChange"
            @submit="addTasksDialog.submit"
        />

        <TaskCreateDialog
            v-model:visible="taskCreateDialog.visible.value"
            v-model:form-data="taskCreateDialog.formData.value"
            :validation-errors="taskCreateDialog.validationErrors.value"
            :is-pending="taskCreateDialog.isPending.value"
            @submit="taskCreateDialog.submit"
        />
    </div>
</template>
