<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import type { TaskListUpsertFormData } from '../composables/use.task-list-upsert-dialog'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { InputContainer } from '@/shared/components/input'

const props = defineProps<{
    visible: boolean
    mode: 'create' | 'update'
    formData: TaskListUpsertFormData
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    'update:visible': [value: boolean]
    'update:formData': [value: TaskListUpsertFormData]
    submit: []
}>()

const title = { create: 'New Task List', update: 'Edit Task List' }

function handleFieldChanged(key: string, value: unknown) {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>

<template>
    <Dialog
        :visible="props.visible"
        :header="title[props.mode]"
        modal
        :closable="!props.isPending"
        :style="{ width: '28rem' }"
        @update:visible="emit('update:visible', $event)"
    >
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="emit('submit')">
            <InputContainer label="Name" :error="props.validationErrors.name" required>
                <InputText
                    :value="props.formData.name"
                    placeholder="e.g. Sprint 1"
                    :invalid="!!props.validationErrors.name"
                    class="w-full"
                    @input="handleFieldChanged('name', ($event.target as HTMLInputElement).value)"
                />
            </InputContainer>

            <InputContainer label="Project" :error="props.validationErrors.project_id">
                <ProjectLookupField
                    :model-value="props.formData.project"
                    :object="true"
                    :disabled="true"
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
                :disabled="props.isPending"
                @click="emit('update:visible', false)"
            />
            <Button
                :label="props.mode === 'create' ? 'Create' : 'Save'"
                :loading="props.isPending"
                :disabled="!props.formData.name.trim()"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>
