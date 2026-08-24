<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import { InputContainer } from '@/shared/components/input'
import { IconPickerField } from '@/shared/components/icon-picker'
import type { ProjectCreateFormData } from '../composables/use.project-create-dialog'

const visible = defineModel<boolean>('visible', { default: false })
const formData = defineModel<ProjectCreateFormData>('formData', { required: true })

defineProps<{
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    submit: []
}>()
</script>

<template>
    <Dialog v-model:visible="visible" header="New Project" modal :closable="!isPending" :style="{ width: '28rem' }">
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="emit('submit')">
            <InputContainer label="Project Name" :error="validationErrors.name" required>
                <div class="gap-2 flex items-center">
                    <IconPickerField v-model="formData.icon" class="shrink-0" />
                    <InputText
                        v-model="formData.name"
                        placeholder="e.g. Atlas Platform"
                        :invalid="!!validationErrors.name"
                        class="flex-1"
                        autofocus
                    />
                </div>
            </InputContainer>
        </form>

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button label="Create" :loading="isPending" :disabled="!formData.name.trim()" @click="emit('submit')" />
        </template>
    </Dialog>
</template>
