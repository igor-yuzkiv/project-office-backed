<script setup lang="ts">
import { ref, watch } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'
import { InputContainer } from '@/shared/components/input'

const visible = defineModel<boolean>('visible', { default: false })

const props = defineProps<{
    version: IProjectDocumentVersion | null
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'submit', label: string | null): void
}>()

const label = ref('')

watch(visible, (open) => {
    if (open) label.value = props.version?.label ?? ''
})

function submit() {
    emit('submit', label.value.trim() || null)
}
</script>

<template>
    <Dialog
        v-model:visible="visible"
        :header="version ? `Rename version ${version.version_number}` : 'Rename version'"
        modal
        :closable="!isPending"
        :style="{ width: '24rem' }"
    >
        <form class="pt-1" @submit.prevent="submit">
            <InputContainer label="Name">
                <InputText v-model="label" placeholder="Leave empty for no name" class="w-full" />
            </InputContainer>
        </form>

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button label="Save" :loading="isPending" @click="submit" />
        </template>
    </Dialog>
</template>
