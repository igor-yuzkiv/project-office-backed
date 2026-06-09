<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import type { AttachmentRole, ModuleName } from '@/shared/types'
import { useAttachmentUpload } from '../composables/use.attachment-upload'

const props = withDefaults(
    defineProps<{
        entityType: ModuleName
        entityId: string
        role?: AttachmentRole | null
        maxFileSizeBytes?: number
    }>(),
    { role: null }
)

const fileInputRef = ref<HTMLInputElement>()

const { uploadFile, isPending } = useAttachmentUpload({
    entityType: () => props.entityType,
    entityId: () => props.entityId,
    role: () => props.role,
    maxFileSizeBytes: () => props.maxFileSizeBytes,
})

function openFilePicker() {
    fileInputRef.value?.click()
}

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    if (file) uploadFile(file)
    input.value = ''
}
</script>

<template>
    <Button label="Upload" severity="info" text :disabled="isPending" @click="openFilePicker">
        <template #icon>
            <Icon icon="material-symbols-light:upload-rounded" class="text-lg" />
        </template>
    </Button>
    <input ref="fileInputRef" type="file" class="hidden" @change="handleFileChange" />
</template>
