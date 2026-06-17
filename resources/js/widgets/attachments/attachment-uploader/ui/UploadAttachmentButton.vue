<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'

const props = withDefaults(
    defineProps<{
        isUploading?: boolean
    }>(),
    { isUploading: false }
)

const emit = defineEmits<{
    'file-selected': [file: File]
}>()

const fileInputRef = ref<HTMLInputElement>()

function openFilePicker() {
    fileInputRef.value?.click()
}

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    if (file) emit('file-selected', file)
    input.value = ''
}
</script>

<template>
    <Button label="Upload" severity="info" text :disabled="props.isUploading" @click="openFilePicker">
        <template #icon>
            <Icon icon="material-symbols-light:upload-rounded" class="text-lg" />
        </template>
    </Button>
    <input ref="fileInputRef" type="file" class="hidden" @change="handleFileChange" />
</template>
