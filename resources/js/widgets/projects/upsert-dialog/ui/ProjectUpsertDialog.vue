<script setup lang="ts">
import { ref } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import type { LaravelValidationErrors } from '@/shared/types'
import type { ProjectStatusValue } from '@/entities/project/types'
import { projectStatusOptions } from '@/entities/project/config'
import type { ITag } from '@/entities/tag/types'
import { InputContainer } from '@/shared/components/input'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { IconButton } from '@/shared/components/button'

const props = defineProps<{
    visible: boolean
    mode: 'create' | 'update'
    name: string
    status: ProjectStatusValue
    tags: ITag[]
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    'update:visible': [value: boolean]
    'update:name': [value: string]
    'update:status': [value: ProjectStatusValue]
    'update:tags': [value: ITag[]]
    submit: []
}>()

const title = { create: 'New Project', update: 'Edit Project' }
const statusOptions = projectStatusOptions()

const showManageTagsDialog = ref(false)

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
            <InputContainer label="Status" :error="props.validationErrors.status" required>
                <Select
                    :model-value="props.status"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    :invalid="!!props.validationErrors.status"
                    class="w-full"
                    @update:model-value="emit('update:status', $event)"
                />
            </InputContainer>
            <InputContainer label="Tags" :error="props.validationErrors.tag_ids">
                <div class="gap-2 flex items-center">
                    <IconButton
                        size="medium"
                        severity="success"
                        icon="mdi:tag-edit"
                        @click="showManageTagsDialog = true"
                    />

                    <TagList :tags="props.tags" />
                </div>
            </InputContainer>
        </form>

        <ManageRecordTagsDialog
            v-model:visible="showManageTagsDialog"
            :model-value="props.tags"
            @update:model-value="emit('update:tags', $event)"
        />

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
