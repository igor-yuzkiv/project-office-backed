<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { projectDocumentStatusOptions } from '@/entities/project-document'
import type { ProjectDocumentPathNodeDto } from '@/entities/project-document/types'
import { IconButton } from '@/shared/components/button'
import { CopyToClipboard } from '@/shared/components/display'
import { InputContainer } from '@/shared/components/input'
import type { LaravelValidationErrors } from '@/shared/types'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { TagList } from '@/widgets/tags/metadata'
import type { ProjectDocumentUpsertFormData } from '../composables/use.project-document-upsert-dialog'

const visible = defineModel<boolean>('visible', { default: false })
const formData = defineModel<ProjectDocumentUpsertFormData>('formData', { required: true })

defineProps<{
    mode: 'create' | 'edit'
    validationErrors: LaravelValidationErrors
    isPending: boolean
    parentDocument?: ProjectDocumentPathNodeDto | null
}>()

const emit = defineEmits<{
    (e: 'submit'): void
}>()

const showManageTagsDialog = ref(false)

function handleFieldChanged<K extends keyof ProjectDocumentUpsertFormData>(
    key: K,
    value: ProjectDocumentUpsertFormData[K]
) {
    formData.value = { ...formData.value, [key]: value }
}
</script>

<template>
    <Dialog
        v-model:visible="visible"
        :header="mode === 'edit' ? 'Edit Document' : 'New Document'"
        modal
        :closable="!isPending"
        :style="{ width: '28rem' }"
    >
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="emit('submit')">
            <!-- Where the document sits is changed by Move, not here. -->
            <InputContainer v-if="parentDocument" label="Parent">
                <div
                    class="gap-2 px-3 py-2 text-surface-700 dark:text-surface-300 rounded border-surface-200 dark:border-surface-700 flex items-center border"
                >
                    <CopyToClipboard :text="parentDocument.key" hide-copy-icon class="text-surface-500" />
                    <span>{{ parentDocument.title }}</span>
                </div>
            </InputContainer>

            <InputContainer label="Title" :error="validationErrors.title" required>
                <InputText
                    :model-value="formData.title"
                    placeholder="e.g. Architecture Overview"
                    :invalid="!!validationErrors.title"
                    class="w-full"
                    @update:model-value="handleFieldChanged('title', $event ?? '')"
                />
            </InputContainer>

            <InputContainer label="Status" :error="validationErrors.status">
                <Select
                    :model-value="formData.status"
                    :options="projectDocumentStatusOptions()"
                    option-label="label"
                    option-value="value"
                    :invalid="!!validationErrors.status"
                    class="w-full"
                    @update:model-value="handleFieldChanged('status', $event)"
                />
            </InputContainer>

            <InputContainer label="Tags" :error="validationErrors.tag_ids">
                <div class="gap-2 p-1 flex items-center">
                    <IconButton size="medium" severity="success" icon="mdi:tag-edit" @click="showManageTagsDialog = true" />
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
                :label="mode === 'edit' ? 'Save' : 'Create'"
                :loading="isPending"
                :disabled="!formData.title.trim()"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>
