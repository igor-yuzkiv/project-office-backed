<script setup lang="ts">
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import { InputContainer } from '@/shared/components/input'

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
            <InputContainer label="Project Name" :error="props.validationErrors.name" required>
                <InputText
                    :value="props.name"
                    placeholder="e.g. Atlas Platform"
                    :invalid="!!props.validationErrors.name"
                    class="w-full"
                    @input="emit('update:name', ($event.target as HTMLInputElement).value)"
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
                :disabled="!props.name.trim()"
                @click="emit('submit')"
            />
        </template>
    </Dialog>
</template>
