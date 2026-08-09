<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import type { TaskListCreateFormData } from '../composables/use.task-list-create-dialog'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { InputContainer } from '@/shared/components/input'

const props = defineProps<{
    visible: boolean
    formData: TaskListCreateFormData
    validationErrors: LaravelValidationErrors
    isPending: boolean
    /** Set when the project comes from the context the dialog was opened in. */
    projectLocked?: boolean
}>()

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void
    (e: 'update:formData', value: TaskListCreateFormData): void
    (e: 'submit'): void
}>()

function handleFieldChanged(key: string, value: unknown) {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>

<template>
    <Dialog
        :visible="visible"
        header="New Task List"
        modal
        :closable="!isPending"
        :style="{ width: '28rem' }"
        @update:visible="emit('update:visible', $event)"
    >
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="emit('submit')">
            <InputContainer label="Name" :error="validationErrors.name" required>
                <InputText
                    :model-value="formData.name"
                    @update:modelValue="handleFieldChanged('name', $event)"
                    placeholder="e.g. Sprint 1"
                    :invalid="!!validationErrors.name"
                    class="w-full"
                />
            </InputContainer>

            <InputContainer label="Project" :error="validationErrors.project_id" required>
                <ProjectLookupField
                    :model-value="formData.project"
                    :object="true"
                    :disabled="projectLocked"
                    class="w-full"
                    fluid
                    @update:model-value="handleFieldChanged('project', $event)"
                />
            </InputContainer>
        </form>

        <template #footer>
            <Button
                label="Cancel"
                severity="secondary"
                text
                :disabled="isPending"
                @click="emit('update:visible', false)"
            />
            <Button
                label="Create"
                :loading="isPending"
                :disabled="!formData.name.trim() || !formData.project"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>
