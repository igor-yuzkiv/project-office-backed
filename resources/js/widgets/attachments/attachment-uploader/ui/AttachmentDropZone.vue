<script setup lang="ts">
import { ref } from 'vue'
import { useDropZone } from '@vueuse/core'
import { useToast } from '@/shared/composables'

const props = withDefaults(
    defineProps<{
        isUploading?: boolean
        maxFileSizeBytes?: number
    }>(),
    { isUploading: false }
)

const emit = defineEmits<{
    'file-drop': [file: File]
}>()

const DEFAULT_MAX_FILE_SIZE = 25 * 1024 * 1024

const toast = useToast()
const dropZoneRef = ref<HTMLElement>()

function onDrop(files: File[] | null) {
    if (props.isUploading || !files || files.length === 0) return

    if (files.length > 1) {
        toast.info('Only the first file will be uploaded.')
    }

    const file = files[0]
    const maxSize = props.maxFileSizeBytes ?? DEFAULT_MAX_FILE_SIZE

    if (file.size > maxSize) {
        toast.error('File exceeds the maximum allowed size.')
        return
    }

    emit('file-drop', file)
}

const { isOverDropZone } = useDropZone(dropZoneRef, { onDrop })
</script>

<template>
    <div ref="dropZoneRef" class="relative flex flex-1 flex-col overflow-hidden">
        <slot />
        <Transition name="fade">
            <div
                v-if="isOverDropZone && !isUploading"
                class="inset-0 rounded-lg border-primary bg-primary/10 absolute z-10 flex items-center justify-center border-2 border-dashed"
            >
                <span class="text-primary font-medium">Drop file to upload</span>
            </div>
        </Transition>
    </div>
</template>
