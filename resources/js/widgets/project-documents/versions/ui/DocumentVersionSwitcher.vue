<script setup lang="ts">
import { computed, ref } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'

// Switching only changes which version is on screen. Which one is primary is a property of the
// document, and nothing here writes it.
const props = defineProps<{
    versions: IProjectDocumentVersion[]
    openVersionId: string | null
}>()

const emit = defineEmits<{
    (e: 'open', version: IProjectDocumentVersion): void
}>()

const popover = ref<InstanceType<typeof Popover>>()

function open(version: IProjectDocumentVersion, event: MouseEvent) {
    emit('open', version)
    popover.value?.toggle(event)
}

const openVersion = computed(() => props.versions.find((version) => version.id === props.openVersionId))
</script>

<template>
    <div class="gap-1 flex items-center">
        <Button label="Versions" size="small" text severity="secondary" @click="popover?.toggle($event)">
            <template #icon><Icon icon="heroicons:clock" class="mr-1 text-base" /></template>
        </Button>

        <span class="text-surface-500 text-xs font-medium">v{{ openVersion?.version_number }}</span>

        <Icon
            v-if="openVersion?.is_primary"
            icon="heroicons:bookmark-solid"
            class="text-primary-500 text-sm"
            aria-label="Primary version"
        />
    </div>

    <Popover ref="popover">
        <ul class="w-64 text-sm max-h-[60vh] overflow-y-auto">
            <li v-for="version in versions" :key="version.id">
                <button
                    type="button"
                    class="gap-2 rounded px-2 py-1.5 hover:bg-surface-100 dark:hover:bg-surface-800 flex w-full items-center text-left"
                    :class="{ 'bg-surface-100 dark:bg-surface-800': version.id === openVersionId }"
                    @click="open(version, $event)"
                >
                    <span class="text-surface-500 w-8 shrink-0 text-xs">v{{ version.version_number }}</span>
                    <span class="min-w-0 flex-1 truncate">{{ version.label ?? 'Untitled' }}</span>
                    <Icon
                        v-if="version.is_primary"
                        icon="heroicons:bookmark-solid"
                        class="text-primary-500 shrink-0 text-sm"
                        aria-label="Primary version"
                    />
                </button>
            </li>
        </ul>
    </Popover>
</template>
