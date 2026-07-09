<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import type { ProjectDocumentCreateFormData } from '../composables/use.project-document-create-dialog'
import type { ProjectDocumentPathNodeDto } from '@/entities/project-document/types'
import { InputContainer } from '@/shared/components/input'
import { CopyToClipboard } from '@/shared/components/display'

const visible = defineModel<boolean>('visible', { default: false })
const formData = defineModel<ProjectDocumentCreateFormData>('formData', { required: true })

defineProps<{
    validationErrors: LaravelValidationErrors
    isPending: boolean
    parentDocument?: ProjectDocumentPathNodeDto | null
}>()

const emit = defineEmits<{
    (e: 'submit'): void
}>()

function handleTitleChanged(value: string | undefined) {
    formData.value = { ...formData.value, title: value ?? '' }
}
</script>

<template>
    <Dialog v-model:visible="visible" header="New Document" modal :closable="!isPending" :style="{ width: '28rem' }">
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="emit('submit')">
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
                    @update:model-value="handleTitleChanged"
                    placeholder="e.g. Architecture Overview"
                    :invalid="!!validationErrors.title"
                    class="w-full"
                />
            </InputContainer>
        </form>

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button label="Create" :loading="isPending" :disabled="!formData.title.trim()" @click="emit('submit')" />
        </template>
    </Dialog>
</template>
