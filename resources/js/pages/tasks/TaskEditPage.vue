<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { refDebounced } from '@vueuse/core'
import { MarkdownEditor } from '@/shared/components/md-editor'
import AutoComplete from 'primevue/autocomplete'
import Panel from 'primevue/panel'
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
import { useLoadingStateStore } from '@/app/stores/use.loading-state.store'

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
const loadingStore = useLoadingStateStore()
const toast = useToast()
const taskId = route.params.id as string
const { task, isPending: isTaskPending, isError } = useTaskQuery(taskId)
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
        },
    )
}

function cancel() {
    router.push({ name: 'task-details', params: { id: taskId } })
}

watch(
    isTaskPending,
    (pending) => {
        if (pending) {
            loadingStore.start('task-edit-load')
        } else {
            loadingStore.stop('task-edit-load')
        }
    },
    { immediate: true },
)

watch(isError, (error) => {
    if (error) {
        loadingStore.stop('task-edit-load')
        toast.error('Failed to load task.')
    }
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
    { immediate: true },
)

onMounted(() => {
    layoutStore.setHeaderActions([
        { key: 'save-task', title: 'Save', action: submit, is_primary: true },
        { key: 'cancel-task', title: 'Cancel', action: cancel },
    ])
})

onUnmounted(() => {
    layoutStore.clearHeaderActions()
    loadingStore.stop('task-edit-load')
})
</script>

<template>
    <div class="p-6 gap-6 flex flex-1 flex-col overflow-auto">
        <template v-if="task">
            <div class="gap-1 flex flex-col">
                <InputText
                    v-model="formData.name"
                    class="text-xl font-semibold w-full"
                    placeholder="Task name..."
                    :invalid="!!validationErrors.name"
                />
                <span v-if="validationErrors.name" class="text-xs text-red-500">{{ validationErrors.name[0] }}</span>
            </div>

            <Panel header="Task Information" toggleable>
                <div class="md:grid-cols-2 gap-4 grid grid-cols-2">
                    <div class="gap-1 flex flex-col">
                        <span class="text-xs font-medium text-surface-400 tracking-wide uppercase">Status</span>
                        <Select
                            v-model="formData.status"
                            :options="taskStatusOptions()"
                            option-label="label"
                            option-value="value"
                            :invalid="!!validationErrors.status"
                        />
                        <span v-if="validationErrors.status" class="text-xs text-red-500">
                            {{ validationErrors.status[0] }}
                        </span>
                    </div>

                    <div class="gap-1 flex flex-col">
                        <span class="text-xs font-medium text-surface-400 tracking-wide uppercase">Priority</span>
                        <Select
                            v-model="formData.priority"
                            :options="taskPriorityOptions()"
                            option-label="label"
                            option-value="value"
                            show-clear
                            :invalid="!!validationErrors.priority"
                        />
                        <span v-if="validationErrors.priority" class="text-xs text-red-500">
                            {{ validationErrors.priority[0] }}
                        </span>
                    </div>

                    <div class="gap-1 flex flex-col">
                        <span class="text-xs font-medium text-surface-400 tracking-wide uppercase">Task List</span>
                        <AutoComplete
                            input-class="w-full"
                            v-model="formData.taskList"
                            :suggestions="taskLists"
                            :loading="isTaskListsLoading"
                            option-label="name"
                            force-selection
                            @complete="taskListSearchTerm = $event.query"
                            dropdown
                            :invalid="!!validationErrors.task_list_id"
                        />
                        <span v-if="validationErrors.task_list_id" class="text-xs text-red-500">
                            {{ validationErrors.task_list_id[0] }}
                        </span>
                    </div>
                </div>
            </Panel>

            <Panel header="Description" toggleable>
                <MarkdownEditor v-model="formData.description" :preview="true" />
            </Panel>
        </template>
    </div>
</template>
