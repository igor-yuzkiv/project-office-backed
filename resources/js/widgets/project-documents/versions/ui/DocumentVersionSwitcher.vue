<script setup lang="ts">
import { ref } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'
import DocumentVersionList from './DocumentVersionList.vue'

// Switching only changes which version is on screen. Which one is primary belongs to the
// document, and a reader's switcher cannot write it.
defineProps<{
    versions: IProjectDocumentVersion[]
    openVersionId: string | null
}>()

const emit = defineEmits<{
    (e: 'open', version: IProjectDocumentVersion): void
}>()

const popover = ref<InstanceType<typeof Popover>>()

function open(version: IProjectDocumentVersion) {
    emit('open', version)
    popover.value?.hide()
}
</script>

<template>
    <Button label="Versions" size="small" text severity="secondary" @click="popover?.toggle($event)">
        <template #icon><Icon icon="heroicons:clock" class="mr-1 text-base" /></template>
    </Button>

    <Popover ref="popover">
        <DocumentVersionList
            class="w-64 max-h-[50vh]"
            :versions="versions"
            :open-version-id="openVersionId"
            @open="open"
        />
    </Popover>
</template>
