<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MarkdownEditor } from '@/shared/components/md-editor'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
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
import { useBreadcrumbs } from '@/app/shell'
import { CopyableKey, PropertiesGrid } from '@/shared/components/display'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'

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
const { mutate: updateTaskList, isPending: isSaving } = useUpdateTaskListMutation()

const formData = ref<TaskListEditFormData>({
    name: '',
    description: '',
    status: 'open',
    tags: [],
})
const savedSnapshot = ref<string | null>(null)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

const isDirty = computed(() => savedSnapshot.value !== null && snapshot(formData.value) !== savedSnapshot.value)

// The title reads as a heading, not a field: no chrome until it is focused.
const titleInputPt = {
    root: {
        class: [
            '!type-title text-ink placeholder:text-ink-3 mt-1.5 mb-3 w-full !rounded-none !border-0 !bg-transparent !px-0 !py-1 !shadow-none',
            'focus:!shadow-[inset_0_-2px_0_var(--color-accent)]',
        ].join(' '),
    },
}

function snapshot(data: TaskListEditFormData): string {
    return JSON.stringify({
        name: data.name,
        description: data.description,
        status: data.status,
        tagIds: data.tags.map((tag) => tag.id).sort(),
    })
}

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
        if (list && savedSnapshot.value === null) {
            formData.value = {
                name: list.name,
                description: list.description ?? '',
                status: list.status,
                tags: list.tags ?? [],
            }
            savedSnapshot.value = snapshot(formData.value)
            layoutStore.setPageTitle(`${list.key} | ${list.name}`)
        }
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
    {
        label: taskList.value?.key ?? 'Task list',
        to: { name: 'task-list-details', params: { id: taskListId } },
    },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="taskList" class="min-h-0 flex flex-1 flex-col overflow-auto">
        <form class="page-container min-h-0 pb-6 flex flex-1 flex-col" @submit.prevent="submit">
            <div class="type-meta gap-1 flex items-center">
                <CopyableKey :value="taskList.key" />
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
                placeholder="Task list name"
                aria-label="Task list name"
                :invalid="!!validationErrors.name"
                :pt="titleInputPt"
            />
            <p v-if="validationErrors.name" class="text-red-500 -mt-2 mb-3 text-[12.5px]">
                {{ validationErrors.name[0] }}
            </p>

            <PropertiesGrid>
                <span>Status</span>
                <div>
                    <Select
                        v-model="formData.status"
                        :options="taskListStatusOptions()"
                        option-label="label"
                        option-value="value"
                        size="small"
                        class="min-w-[180px]"
                        :invalid="!!validationErrors.status"
                    >
                        <template #value="{ value }">
                            <TaskListStatusTag :status="value" />
                        </template>
                        <template #option="{ option }">
                            <TaskListStatusTag :status="option.value" />
                        </template>
                    </Select>
                    <span v-if="validationErrors.status" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.status[0] }}
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
    </div>
</template>
