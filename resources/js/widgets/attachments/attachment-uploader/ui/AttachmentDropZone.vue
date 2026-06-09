<script setup lang="ts">
import { ref } from 'vue'
import { useDropZone } from '@vueuse/core'
import type { AttachmentRole, ModuleName } from '@/shared/types'
import { useToast } from '@/shared/composables'
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

const toast = useToast()
const dropZoneRef = ref<HTMLElement>()

const { uploadFile, isPending } = useAttachmentUpload({
    entityType: () => props.entityType,
    entityId: () => props.entityId,
    role: () => props.role,
    maxFileSizeBytes: () => props.maxFileSizeBytes,
})

function onDrop(files: File[] | null) {
    if (isPending.value || !files || files.length === 0) return

    if (files.length > 1) {
        toast.info('Only the first file will be uploaded.')
    }

    uploadFile(files[0])
}

const { isOverDropZone } = useDropZone(dropZoneRef, { onDrop })
</script>

<template>
    <div ref="dropZoneRef" class="relative flex flex-1 flex-col overflow-hidden">
        <slot />
        <Transition name="fade">
            <div
                v-if="isOverDropZone && !isPending"
                class="inset-0 rounded-lg border-primary bg-primary/10 absolute z-10 flex items-center justify-center border-2 border-dashed"
            >
                <span class="text-primary font-medium">Drop file to upload</span>
            </div>
        </Transition>
    </div>
</template>
