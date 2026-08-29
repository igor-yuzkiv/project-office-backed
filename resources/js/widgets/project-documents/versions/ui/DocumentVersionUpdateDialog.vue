<script setup lang="ts">
import { ref, watch } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import type { IProjectDocumentVersion, IUpdateProjectDocumentVersionInput } from '@/entities/project-document/types'
import { InputContainer } from '@/shared/components/input'
import type { LaravelValidationErrors } from '@/shared/types'

const visible = defineModel<boolean>('visible', { default: false })

const props = defineProps<{
    version: IProjectDocumentVersion | null
    validationErrors: LaravelValidationErrors
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'submit', input: IUpdateProjectDocumentVersionInput): void
}>()

// The version's own fields. Content is not one of them — it is written on the sheet.
const label = ref('')

watch(visible, (open) => {
    if (open) label.value = props.version?.label ?? ''
})

function submit() {
    emit('submit', { label: label.value.trim() || null })
}
</script>

<template>
    <Dialog
        v-model:visible="visible"
        :header="version ? `Edit version ${version.version_number}` : 'Edit version'"
        modal
        :closable="!isPending"
        :style="{ width: '24rem' }"
    >
        <form class="pt-1" @submit.prevent="submit">
            <InputContainer label="Name" :error="validationErrors.label">
                <InputText
                    v-model="label"
                    placeholder="Leave empty for no name"
                    :invalid="!!validationErrors.label"
                    class="w-full"
                />
            </InputContainer>
        </form>

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button label="Save" :loading="isPending" @click="submit" />
        </template>
    </Dialog>
</template>
