<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import type { ProjectDocumentCreateFormData } from '../composables/use.project-document-create-dialog'
import { InputContainer } from '@/shared/components/input'

const visible = defineModel<boolean>('visible', { default: false })
const formData = defineModel<ProjectDocumentCreateFormData>('formData', { required: true })

defineProps<{
    validationErrors: LaravelValidationErrors
    isPending: boolean
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
