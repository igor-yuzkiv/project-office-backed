<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import type { TaskCreateFormData } from '../composables/use.task-create-dialog'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { TaskListLookupField } from '@/widgets/task-list/lookup-field'
import { InputContainer } from '@/shared/components/input'

const visible = defineModel<boolean>('visible', { default: false })
const formData = defineModel<TaskCreateFormData>('formData', { required: true })

defineProps<{
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    submit: []
}>()

function handleFieldChanged(key: keyof TaskCreateFormData, value: unknown) {
    formData.value = { ...formData.value, [key]: value }

    if (key === 'project') {
        formData.value = { ...formData.value, taskList: null }
    }
}
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
                <TaskListLookupField
                    :model-value="formData.taskList"
                    :project-id="formData.project.id"
                    :object="true"
                    :invalid="!!validationErrors.task_list_id"
                    @update:model-value="handleFieldChanged('taskList', $event)"
                />
            </InputContainer>
        </form>

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
