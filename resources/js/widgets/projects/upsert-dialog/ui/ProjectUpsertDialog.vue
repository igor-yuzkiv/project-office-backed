<script setup lang="ts">
import { ref } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import { projectStatusOptions } from '@/entities/project/config'
import { InputContainer } from '@/shared/components/input'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { IconButton } from '@/shared/components/button'
import type { ProjectUpsertFormData } from '../composables/use.project-upsert-dialog'

const visible = defineModel<boolean>('visible', { default: false })
const formData = defineModel<ProjectUpsertFormData>('formData', { required: true })

defineProps<{
    mode: 'create' | 'update'
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    submit: []
}>()

const title = { create: 'New Project', update: 'Edit Project' }
const statusOptions = projectStatusOptions()

const showManageTagsDialog = ref(false)

function handleFieldChanged(key: keyof ProjectUpsertFormData, value: unknown) {
    formData.value = { ...formData.value, [key]: value }
}
</script>

<template>
    <Dialog v-model:visible="visible" :header="title[mode]" modal :closable="!isPending" :style="{ width: '28rem' }">
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="emit('submit')">
            <InputContainer label="Project Name" :error="validationErrors.name" required>
                <InputText
                    :model-value="formData.name"
                    @update:model-value="handleFieldChanged('name', $event)"
                    placeholder="e.g. Atlas Platform"
                    :invalid="!!validationErrors.name"
                    class="w-full"
                />
            </InputContainer>
            <InputContainer label="Status" :error="validationErrors.status" required>
                <Select
                    :model-value="formData.status"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    :invalid="!!validationErrors.status"
                    class="w-full"
                    @update:model-value="handleFieldChanged('status', $event)"
                />
            </InputContainer>
            <InputContainer label="Tags" :error="validationErrors.tag_ids">
                <div class="gap-2 flex items-center">
                    <IconButton
                        size="medium"
                        severity="success"
                        icon="mdi:tag-edit"
                        @click="showManageTagsDialog = true"
                    />

                    <TagList :tags="formData.tags" />
                </div>
            </InputContainer>
        </form>

        <ManageRecordTagsDialog
            v-model:visible="showManageTagsDialog"
            :model-value="formData.tags"
            @update:model-value="handleFieldChanged('tags', $event)"
        />

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button
                :label="mode === 'create' ? 'Create' : 'Save'"
                :loading="isPending"
                :disabled="!formData.name.trim()"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>
