<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import type { TaskCreateFormData } from '../composables/use.task-create-dialog'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { TaskListLookupField } from '@/widgets/task-list/lookup-field'
import { TaskListCreateDialog, useTaskListCreateDialog } from '@/widgets/task-list/create-dialog'
import { InputContainer } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import type { ITaskList } from '@/entities/task-list/types'

const visible = defineModel<boolean>('visible', { default: false })
const formData = defineModel<TaskCreateFormData>('formData', { required: true })

defineProps<{
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'submit'): void
}>()

function handleFieldChanged(key: keyof TaskCreateFormData, value: unknown) {
    const nextValue = { ...formData.value, [key]: value }

    if (key === 'project') {
        nextValue.taskList = null
    }

    formData.value = nextValue
}

// A list created here belongs to the task's project and is selected right away, so the user
// never has to leave the form to make one.
const taskListCreateDialog = useTaskListCreateDialog({
    onCreated: (taskList: ITaskList) => handleFieldChanged('taskList', taskList),
})
</script>

<template>
    <Dialog v-model:visible="visible" header="New Task" modal :closable="!isPending" :style="{ width: '28rem' }">
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="emit('submit')">
            <InputContainer label="Task Name" :error="validationErrors.name" required>
                <InputText
                    :model-value="formData.name"
                    @update:model-value="handleFieldChanged('name', $event)"
                    placeholder="e.g. Fix login bug"
                    :invalid="!!validationErrors.name"
                    class="w-full"
                />
            </InputContainer>

            <InputContainer label="Project" :error="validationErrors.project_id" required>
                <ProjectLookupField
                    :model-value="formData.project"
                    :object="true"
                    :invalid="!!validationErrors.project_id"
                    class="w-full"
                    fluid
                    @update:model-value="handleFieldChanged('project', $event)"
                />
            </InputContainer>

            <InputContainer v-if="formData.project" label="Task List" :error="validationErrors.task_list_id">
                <div class="gap-2 flex items-center">
                    <TaskListLookupField
                        :model-value="formData.taskList"
                        :project-id="formData.project.id"
                        :object="true"
                        :invalid="!!validationErrors.task_list_id"
                        class="min-w-0 flex-1"
                        @update:model-value="handleFieldChanged('taskList', $event)"
                    />
                    <IconButton
                        icon="material-symbols:add"
                        severity="success"
                        title="New task list"
                        @click="taskListCreateDialog.open(formData.project)"
                    />
                </div>
            </InputContainer>
        </form>

        <TaskListCreateDialog
            :visible="taskListCreateDialog.visible.value"
            project-locked
            :form-data="taskListCreateDialog.formData.value"
            :validation-errors="taskListCreateDialog.validationErrors.value"
            :is-pending="taskListCreateDialog.isPending.value"
            @update:visible="taskListCreateDialog.visible.value = $event"
            @update:form-data="taskListCreateDialog.formData.value = $event"
            @submit="taskListCreateDialog.submit()"
        />

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button
                label="Create"
                :loading="isPending"
                :disabled="!formData.name.trim() || !formData.project"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>
