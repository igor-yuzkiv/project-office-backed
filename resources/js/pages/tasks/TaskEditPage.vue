<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { refDebounced } from '@vueuse/core'
import { MarkdownEditor } from '@/shared/components/md-editor'
import Panel from 'primevue/panel'
import { InputContainer, LookupField } from '@/shared/components/input'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { taskPriorityOptions, taskStatusOptions } from '@/entities/task/config'
import { useTaskQuery } from '@/entities/task/queries'
import { useUpdateTaskMutation } from '@/entities/task/mutations'
import type { IUpdateTaskInput, TaskStatusValue } from '@/entities/task/types'
import { useTaskListsSearchQuery } from '@/entities/task_list/queries'
import type { ITaskList } from '@/entities/task_list/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

interface TaskEditFormData {
    name: string
    description: string
    taskList: ITaskList | null
    status: TaskStatusValue
    priority: number | null
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
})
const isFormInitialized = ref(false)
const taskListSearchTerm = ref('')
const debouncedTaskListSearchTerm = refDebounced(taskListSearchTerm, 300)
const validationErrors = ref<LaravelValidationErrors>({})

const taskListSearchParams = computed(() => ({
    query: debouncedTaskListSearchTerm.value,
    project_id: task.value?.project_id,
    per_page: 20,
    page: 1,
}))

const { taskLists, isPending: isTaskListsLoading } = useTaskListsSearchQuery(taskListSearchParams)

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
    }

    updateTask(
        { taskId, data: input },
        {
            onSuccess: () => {
                router.push({ name: 'task-details', params: { id: taskId } })
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
            }
            isFormInitialized.value = true
            layoutStore.setPageTitle(`${t.key} | ${t.name}`)
        }
    },
    { immediate: true }
)

onMounted(() => {
    layoutStore.setHeaderActions([
        { key: 'save-task', title: 'Save', action: submit, is_primary: true },
        { key: 'cancel-task', title: 'Cancel', action: cancel },
    ])
})

onUnmounted(() => {
    layoutStore.clearHeaderActions()
})
</script>

<template>
    <div v-if="task" class="p-2 gap-6 flex flex-1 flex-col overflow-auto">
        <div class="app-card p-3 gap-3 flex flex-col">
            <div class="md:grid-cols-2 gap-3 grid grid-cols-1">
                <InputContainer label="Name" :error="validationErrors.name" required>
                    <InputText v-model="formData.name" placeholder="Task name..." :invalid="!!validationErrors.name" />
                </InputContainer>

                <InputContainer label="Task List" :error="validationErrors.task_list_id">
                    <LookupField
                        v-model="formData.taskList"
                        :options="taskLists"
                        :loading="isTaskListsLoading"
                        option-label="name"
                        input-class="w-full"
                        :invalid="!!validationErrors.task_list_id"
                        show-clear
                        @search="taskListSearchTerm = $event"
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

            <MarkdownEditor
                v-model="formData.description"
                preview
                style="height: 100%"
                image_entity_type="tasks"
                :image_entity_id="taskId"
                image_role="task_description"
            />
        </div>
    </div>
</template>
