<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { InputContainer } from '@/shared/components/input'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { taskListStatusOptions, TaskListAttachmentRoles } from '@/entities/task-list/config'
import { uploadTaskListAttachmentRequest } from '@/entities/task-list/api'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { useUpdateTaskListMutation } from '@/entities/task-list/mutations'
import type { IUpdateTaskListInput, TaskListStatusValue } from '@/entities/task-list/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { IconButton } from '@/shared/components/button'

interface TaskListEditFormData {
    name: string
    description: string
    status: TaskListStatusValue
    tags: ITag[]
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const taskListId = route.params.id as string
const { taskList, isError } = useTaskListQuery(taskListId)
const { mutate: updateTaskList } = useUpdateTaskListMutation()

const formData = ref<TaskListEditFormData>({
    name: '',
    description: '',
    status: 'open',
    tags: [],
})
const isFormInitialized = ref(false)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

function handleError(error: unknown) {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save task list.')
    }
}

function navigateBack() {
    if (window.history.state?.back) {
        router.back()
    } else {
        router.push({ name: 'task-list-details', params: { id: taskListId } })
    }
}

async function handleImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) => uploadTaskListAttachmentRequest(taskListId, file, TaskListAttachmentRoles.DESCRIPTION))
    )
    callback(results.map((res) => res.data.url))
}

function submit() {
    if (!taskList.value) return

    validationErrors.value = {}

    const input: IUpdateTaskListInput = {
        name: formData.value.name,
        description: formData.value.description || null,
        status: formData.value.status,
        tag_ids: formData.value.tags.map((t) => t.id),
    }

    updateTaskList(
        { taskListId, data: input },
        {
            onSuccess: navigateBack,
            onError: handleError,
        }
    )
}

watch(isError, (error) => {
    if (error) toast.error('Failed to load task list.')
})

watch(
    taskList,
    (list) => {
        if (list && !isFormInitialized.value) {
            formData.value = {
                name: list.name,
                description: list.description ?? '',
                status: list.status,
                tags: list.tags ?? [],
            }
            isFormInitialized.value = true
            layoutStore.setPageTitle(`${list.key} | ${list.name}`)
        }
    },
    { immediate: true }
)

useHeaderActions([
    { key: 'save-task-list', title: 'Save', action: submit, is_primary: true },
    { key: 'cancel-task-list', title: 'Cancel', action: navigateBack },
])

useBreadcrumbs(() => [
    { label: 'Task Lists', to: { name: 'task-lists' } },
    ...(taskList.value?.project
        ? [
              {
                  label: taskList.value.project.name,
                  to: { name: 'project-details', params: { id: taskList.value.project_id } },
              },
          ]
        : []),
    {
        label: taskList.value?.key ?? 'Task List',
        to: { name: 'task-list-details', params: { id: taskListId } },
    },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="taskList" class="p-2 flex flex-1 flex-col overflow-hidden">
        <div class="p-3 gap-3 flex flex-col">
            <div class="md:grid-cols-2 gap-3 grid grid-cols-1">
                <InputContainer label="Name" :error="validationErrors.name" required>
                    <InputText
                        v-model="formData.name"
                        placeholder="Task list name..."
                        :invalid="!!validationErrors.name"
                    />
                </InputContainer>

                <InputContainer label="Status" :error="validationErrors.status">
                    <Select
                        v-model="formData.status"
                        :options="taskListStatusOptions()"
                        option-label="label"
                        option-value="value"
                        :invalid="!!validationErrors.status"
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
                :handle-image-upload="handleImageUpload"
            />
        </div>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />
    </div>
</template>
