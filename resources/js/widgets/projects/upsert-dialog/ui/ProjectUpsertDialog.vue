<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'

const props = defineProps<{
    visible: boolean
    mode: 'create' | 'update'
    name: string
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    'update:visible': [value: boolean]
    'update:name': [value: string]
    submit: []
}>()

const title = { create: 'New Project', update: 'Edit Project' }
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
            <div class="gap-1.5 flex flex-col">
                <label for="project-name" class="text-sm font-medium text-surface-700 dark:text-surface-300">
                    Project Name <span class="text-red-500">*</span>
                </label>
                <InputText
                    id="project-name"
                    :value="props.name"
                    placeholder="e.g. Atlas Platform"
                    :invalid="!!props.validationErrors.name"
                    class="w-full"
                    @input="emit('update:name', ($event.target as HTMLInputElement).value)"
                />
                <span v-if="props.validationErrors.name" class="text-xs text-red-500">
                    {{ props.validationErrors.name[0] }}
                </span>
            </div>
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
                :disabled="!props.name.trim()"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>
