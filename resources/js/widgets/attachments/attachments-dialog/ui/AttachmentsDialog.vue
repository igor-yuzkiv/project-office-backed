<script setup lang="ts">
import { computed, ref } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import type { IAttachment } from '@/entities/attachment/types'
import { AttachmentDropZone } from '@/widgets/attachments/attachment-uploader'
import AttachmentList from './AttachmentList.vue'

// The dialog shows the files and reports what the reader did with them. Fetching, uploading and
// deleting stay with the entity that owns the attachments, which is why they arrive as props.
const props = defineProps<{
    attachments: IAttachment[]
    isPending: boolean
    isUploading?: boolean
    upload: (file: File) => Promise<unknown>
    /** Shown before the file count in the header, e.g. the entity key. */
    subtitle?: string
}>()

const emit = defineEmits<{
    (e: 'download', attachment: IAttachment): void
    (e: 'delete', attachment: IAttachment): void
}>()

const visible = defineModel<boolean>('visible', { required: true })

const fileInputRef = ref<HTMLInputElement>()

const headerNote = computed(() => {
    const count = props.attachments.length
    const files = count === 1 ? '1 file' : `${count} files`
    return props.subtitle ? `${props.subtitle} · ${files}` : files
})

function openFilePicker() {
    fileInputRef.value?.click()
}

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    if (file) props.upload(file)
    input.value = ''
}
</script>

<template>
    <Dialog v-model:visible="visible" modal :style="{ width: '40rem' }" :pt="{ content: { class: 'p-2!' } }">
        <template #header>
            <div class="gap-2.5 flex items-center">
                <span class="type-section">Attachments</span>
                <span class="type-meta-3">{{ headerNote }}</span>
            </div>
        </template>

        <AttachmentDropZone :is-uploading="isUploading" @file-drop="upload">
            <button
                type="button"
                class="m-2 px-4 py-5 type-meta border-line-2 hover:border-accent rounded-lg block cursor-pointer border border-dashed text-center"
                :disabled="isUploading"
                @click="openFilePicker"
            >
                Drop a file here or <span class="text-accent font-medium">choose one</span>
                <span class="type-meta-3 block">Up to 25 MB</span>
            </button>

            <AttachmentList
                :attachments="attachments"
                :is-pending="isPending"
                @download="emit('download', $event)"
                @delete="emit('delete', $event)"
            />
        </AttachmentDropZone>

        <template #footer>
            <div class="flex w-full items-center justify-end">
                <Button label="Upload" size="small" :loading="isUploading" @click="openFilePicker" />
            </div>
        </template>

        <input ref="fileInputRef" type="file" class="hidden" @change="onFileChange" />
    </Dialog>
</template>
