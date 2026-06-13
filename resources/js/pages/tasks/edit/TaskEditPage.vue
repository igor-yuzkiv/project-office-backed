<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { InputContainer } from '@/shared/components/input'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { taskPriorityOptions, taskStatusOptions, TASK_MODULE_NAME, TASK_ATTACHMENT_ROLES } from '@/entities/task/config'
import { useTaskQuery } from '@/entities/task/queries'
import { useUpdateTaskMutation } from '@/entities/task/mutations'
import type { IUpdateTaskInput, TaskStatusValue } from '@/entities/task/types'
import type { ITaskList } from '@/entities/task-list/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TaskListLookupField } from '@/widgets/task-list/lookup-field'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { IconButton } from '@/shared/components/button'

interface TaskEditFormData {
    name: string
    description: string
    taskList: ITaskList | null
    status: TaskStatusValue
    priority: number | null
    tags: ITag[]
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const taskId = route.params.id as string
const { task, isError } = useTaskQuery(taskId)
const { mutate: updateTask } = useUpdateTaskMutation()

const formData = ref<TaskEditFormData>({
    name: '',
    description: '',
    taskList: null,
    status: 'open',
    priority: null,
    tags: [],
})
const isFormInitialized = ref(false)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

function handleError(error: unknown) {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save task.')
    }
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
        tag_ids: formData.value.tags.map((t) => t.id),
    }

    updateTask(
        { taskId, data: input },
        {
            onSuccess: () => {
                if (window.history.state?.back) {
                    router.back()
                } else {
                    router.push({ name: 'task-details', params: { id: taskId } })
                }
            },
            onError: handleError,
        }
    )
}

function cancel() {
    router.push({ name: 'task-details', params: { id: taskId } })
}

watch(isError, (error) => {
    if (error) toast.error('Failed to load task.')
})

watch(
    task,
    (t) => {
        if (t && !isFormInitialized.value) {
            formData.value = {
                name: t.name,
                description: t.description ?? '',
                taskList: t.task_list ?? null,
                status: t.status,
                priority: t.priority?.value ?? null,
                tags: t.tags ?? [],
            }
            isFormInitialized.value = true
            layoutStore.setPageTitle(`${t.key} | ${t.name}`)
        }
    },
    { immediate: true }
)

useHeaderActions([
    { key: 'save-task', title: 'Save', action: submit, is_primary: true },
    { key: 'cancel-task', title: 'Cancel', action: cancel },
])

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
    <div v-if="task" class="p-2 flex flex-1 flex-col overflow-hidden">
        <div class="p-3 gap-3 flex flex-col">
            <div class="md:grid-cols-2 gap-3 grid grid-cols-1">
                <InputContainer label="Name" :error="validationErrors.name" required>
                    <InputText v-model="formData.name" placeholder="Task name..." :invalid="!!validationErrors.name" />
                </InputContainer>

                <InputContainer label="Task List" :error="validationErrors.task_list_id">
                    <TaskListLookupField
                        v-model="formData.taskList"
                        :project-id="task?.project_id"
                        :object="true"
                        :invalid="!!validationErrors.task_list_id"
                    />
                </InputContainer>

                <InputContainer label="Status" :error="validationErrors.status">
                    <Select
                        v-model="formData.status"
                        :options="taskStatusOptions()"
                        option-label="label"
                        option-value="value"
                        :invalid="!!validationErrors.status"
                    />
                </InputContainer>

                <InputContainer label="Priority" :error="validationErrors.priority">
                    <Select
                        v-model="formData.priority"
                        :options="taskPriorityOptions()"
                        option-label="label"
                        option-value="value"
                        show-clear
                        :invalid="!!validationErrors.priority"
                    />
                </InputContainer>
            </div>

            <InputContainer label="Tags" :error="validationErrors.tag_ids">
                <div class="gap-2 p-1 flex items-center">
                    <IconButton
                        size="medium"
                        severity="success"
                        icon="mdi:tag-edit"
                        @click="showManageTagsDialog = true"
                    />
                    <TagList :tags="formData.tags" />
                </div>
            </InputContainer>
        </div>

        <div class="flex-1 overflow-auto">
            <MarkdownEditor
                v-model="formData.description"
                preview
                style="height: 100%"
                :image_entity_type="TASK_MODULE_NAME"
                :image_entity_id="taskId"
                :image_role="TASK_ATTACHMENT_ROLES.DESCRIPTION"
            />
        </div>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />
    </div>
</template>
