<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MarkdownEditor } from '@/shared/components/md-editor'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Button from 'primevue/button'
import { taskPriorityOptions, taskStatusOptions, TaskAttachmentRoles } from '@/entities/task/config'
import { uploadTaskAttachmentRequest } from '@/entities/task/api/task-attachments.api'
import { useTaskQuery } from '@/entities/task/queries'
import { useUpdateTaskMutation } from '@/entities/task/mutations'
import type { IUpdateTaskInput, TaskStatusValue } from '@/entities/task/types'
import type { ITaskListOverview } from '@/entities/task-list/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useBreadcrumbs } from '@/app/shell'
import { CopyableKey, PropertiesGrid } from '@/shared/components/display'
import { TaskListLookupField } from '@/widgets/task-list/lookup-field'
import { TaskListCreateDialog, useTaskListCreateDialog } from '@/widgets/task-list/create-dialog'
import type { ITaskList } from '@/entities/task-list/types'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { TaskPriorityBars, TaskStatusTag } from '@/widgets/tasks/metadata'

interface TaskEditFormData {
    name: string
    description: string
    taskList: ITaskListOverview | null
    status: TaskStatusValue
    priority: number | null
    start_date: Date | null
    due_date: Date | null
    tags: ITag[]
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const taskId = route.params.id as string
const { task, isError } = useTaskQuery(taskId)
const { mutate: updateTask, isPending: isSaving } = useUpdateTaskMutation()

const formData = ref<TaskEditFormData>({
    name: '',
    description: '',
    taskList: null,
    status: 'open',
    priority: null,
    start_date: null,
    due_date: null,
    tags: [],
})
const savedSnapshot = ref<string | null>(null)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

const isDirty = computed(() => savedSnapshot.value !== null && snapshot(formData.value) !== savedSnapshot.value)

const priorityOptions = taskPriorityOptions()
const selectedPriority = computed(() => priorityOptions.find((option) => option.value === formData.value.priority))

// The title reads as a heading, not a field: no chrome until it is focused.
const titleInputPt = {
    root: {
        class: [
            '!type-title text-ink placeholder:text-ink-3 mt-1.5 mb-3 w-full !rounded-none !border-0 !bg-transparent !px-0 !py-1 !shadow-none',
            'focus:!shadow-[inset_0_-2px_0_var(--color-accent)]',
        ].join(' '),
    },
}

// A list created here belongs to the task's project and is selected right away, so the user
// never has to leave the form to make one.
const taskListCreateDialog = useTaskListCreateDialog({
    onCreated: (taskList: ITaskList) => {
        formData.value.taskList = taskList
    },
})

function openTaskListCreateDialog() {
    if (!task.value?.project) {
        console.warn('Cannot create a task list: the task has no project.')
        return
    }

    taskListCreateDialog.open(task.value.project)
}

function snapshot(data: TaskEditFormData): string {
    return JSON.stringify({
        name: data.name,
        description: data.description,
        taskListId: data.taskList?.id ?? null,
        status: data.status,
        priority: data.priority,
        startDate: formatDateForApi(data.start_date),
        dueDate: formatDateForApi(data.due_date),
        tagIds: data.tags.map((tag) => tag.id).sort(),
    })
}

function handleError(error: unknown) {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save task.')
    }
}

function formatDateForApi(date: Date | null): string | null {
    if (!date) return null
    return date.toISOString().split('T')[0]
}

function navigateBack() {
    if (window.history.state?.back) {
        router.back()
    } else {
        router.push({ name: 'task-details', params: { id: taskId } })
    }
}

async function handleImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) => uploadTaskAttachmentRequest(taskId, file, TaskAttachmentRoles.DESCRIPTION))
    )
    callback(results.map((res) => res.data.url))
}

function submit() {
    if (!task.value) return

    validationErrors.value = {}

    const input: IUpdateTaskInput = {
        name: formData.value.name,
        description: formData.value.description || null,
        task_list_id: formData.value.taskList?.id ?? null,
        status: formData.value.status,
        priority: formData.value.priority,
        start_date: formatDateForApi(formData.value.start_date),
        due_date: formatDateForApi(formData.value.due_date),
        tag_ids: formData.value.tags.map((t) => t.id),
    }

    updateTask(
        { taskId, data: input },
        {
            onSuccess: navigateBack,
            onError: handleError,
        }
    )
}

watch(isError, (error) => {
    if (error) toast.error('Failed to load task.')
})

watch(
    task,
    (t) => {
        if (t && savedSnapshot.value === null) {
            formData.value = {
                name: t.name,
                description: t.description ?? '',
                taskList: t.task_list ?? null,
                status: t.status,
                priority: t.priority?.value ?? null,
                start_date: t.start_date ? new Date(t.start_date) : null,
                due_date: t.due_date ? new Date(t.due_date) : null,
                tags: t.tags ?? [],
            }
            savedSnapshot.value = snapshot(formData.value)
            layoutStore.setPageTitle(`${t.key} | ${t.name}`)
        }
    },
    { immediate: true }
)

useBreadcrumbs(() => [
    { label: 'Tasks', to: { name: 'tasks' } },
    ...(task.value?.project
        ? [{ label: task.value.project.name, to: { name: 'project-details', params: { id: task.value.project_id } } }]
        : []),
    {
        label: task.value?.key ?? 'Task',
        to: { name: 'task-details', params: { id: taskId } },
    },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="task" class="min-h-0 flex flex-1 flex-col overflow-auto">
        <form class="page-container min-h-0 pb-6 flex flex-1 flex-col" @submit.prevent="submit">
            <div class="type-meta gap-1 flex items-center">
                <CopyableKey :value="task.key" />
                <div class="gap-1 ml-auto flex items-center">
                    <span v-if="isDirty" class="type-meta-3 pr-1.5 whitespace-nowrap">Unsaved changes</span>
                    <Button
                        label="Cancel"
                        size="small"
                        severity="secondary"
                        outlined
                        type="button"
                        @click="navigateBack"
                    />
                    <Button label="Save" size="small" type="submit" :loading="isSaving" />
                </div>
            </div>

            <InputText
                v-model="formData.name"
                placeholder="Task name"
                aria-label="Task name"
                :invalid="!!validationErrors.name"
                :pt="titleInputPt"
            />
            <p v-if="validationErrors.name" class="text-red-500 -mt-2 mb-3 text-[12.5px]">
                {{ validationErrors.name[0] }}
            </p>

            <PropertiesGrid>
                <span>Task list</span>
                <div>
                    <TaskListLookupField
                        v-model="formData.taskList"
                        :project-id="task.project_id"
                        :object="true"
                        :invalid="!!validationErrors.task_list_id"
                        size="small"
                        class="min-w-[240px]"
                    />
                    <Button
                        label="Create new list…"
                        size="small"
                        severity="secondary"
                        outlined
                        type="button"
                        :disabled="!task.project"
                        @click="openTaskListCreateDialog"
                    />
                    <span v-if="validationErrors.task_list_id" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.task_list_id[0] }}
                    </span>
                </div>

                <span>Status</span>
                <div>
                    <Select
                        v-model="formData.status"
                        :options="taskStatusOptions()"
                        option-label="label"
                        option-value="value"
                        size="small"
                        class="min-w-[180px]"
                        :invalid="!!validationErrors.status"
                    >
                        <template #value="{ value }">
                            <TaskStatusTag :status="value" />
                        </template>
                        <template #option="{ option }">
                            <TaskStatusTag :status="option.value" />
                        </template>
                    </Select>
                    <span v-if="validationErrors.status" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.status[0] }}
                    </span>
                </div>

                <span>Priority</span>
                <div>
                    <Select
                        v-model="formData.priority"
                        :options="priorityOptions"
                        option-label="label"
                        option-value="value"
                        size="small"
                        class="min-w-[180px]"
                        :invalid="!!validationErrors.priority"
                    >
                        <template #value>
                            <TaskPriorityBars :priority="selectedPriority" />
                        </template>
                        <template #option="{ option }">
                            <TaskPriorityBars :priority="option" />
                        </template>
                    </Select>
                    <span v-if="validationErrors.priority" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.priority[0] }}
                    </span>
                </div>

                <span>Dates</span>
                <div>
                    <DatePicker
                        v-model="formData.start_date"
                        date-format="yy-mm-dd"
                        placeholder="Start"
                        show-clear
                        size="small"
                        class="w-[150px]"
                        :invalid="!!validationErrors.start_date"
                    />
                    <span class="text-ink-3">→</span>
                    <DatePicker
                        v-model="formData.due_date"
                        date-format="yy-mm-dd"
                        placeholder="Due"
                        show-clear
                        size="small"
                        class="w-[150px]"
                        :invalid="!!validationErrors.due_date"
                    />
                    <span v-if="validationErrors.start_date" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.start_date[0] }}
                    </span>
                    <span v-if="validationErrors.due_date" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.due_date[0] }}
                    </span>
                </div>

                <span>Tags</span>
                <div>
                    <TagList :tags="formData.tags" inline />
                    <Button
                        label="Manage tags"
                        size="small"
                        severity="secondary"
                        outlined
                        type="button"
                        @click="showManageTagsDialog = true"
                    />
                    <span v-if="validationErrors.tag_ids" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.tag_ids[0] }}
                    </span>
                </div>
            </PropertiesGrid>

            <hr class="border-line mt-5 mb-7" />

            <h2 class="type-section mb-2.5">Description</h2>
            <!-- The editor takes the rest of the viewport and scrolls inside; the page scrolls only when it cannot fit. -->
            <MarkdownEditor
                v-model="formData.description"
                preview
                class="min-h-[240px] flex-1"
                min-height="0"
                :handle-image-upload="handleImageUpload"
            />
            <p v-if="validationErrors.description" class="text-red-500 mt-1 text-[12.5px]">
                {{ validationErrors.description[0] }}
            </p>
        </form>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />

        <TaskListCreateDialog
            :visible="taskListCreateDialog.visible.value"
            project-locked
            :form-data="taskListCreateDialog.formData.value"
            :validation-errors="taskListCreateDialog.validationErrors.value"
            :is-pending="taskListCreateDialog.isPending.value"
            @update:visible="taskListCreateDialog.visible.value = $event"
            @update:form-data="taskListCreateDialog.formData.value = $event"
            @submit="taskListCreateDialog.submit"
        />
    </div>
</template>
