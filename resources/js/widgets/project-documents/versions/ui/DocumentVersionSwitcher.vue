<script setup lang="ts">
import { computed, ref } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'

// Switching only changes which version is on screen. Which one is primary is a property of the
// document, and the reader's popover cannot write it — `editable` is what adds that.
const props = withDefaults(
    defineProps<{
        versions: IProjectDocumentVersion[]
        openVersionId: string | null
        editable?: boolean
        dirtyIds?: string[]
        hasPinnedVersion?: boolean
        isBusy?: boolean
    }>(),
    { dirtyIds: () => [] }
)

const emit = defineEmits<{
    (e: 'open', version: IProjectDocumentVersion): void
    (e: 'create'): void
    (e: 'delete', version: IProjectDocumentVersion): void
    (e: 'set-primary', version: IProjectDocumentVersion): void
    (e: 'use-latest-as-primary'): void
}>()

const popover = ref<InstanceType<typeof Popover>>()

const openVersion = computed(() => props.versions.find((version) => version.id === props.openVersionId) ?? null)

function isDirty(version: IProjectDocumentVersion) {
    return props.dirtyIds.includes(version.id)
}

function open(version: IProjectDocumentVersion, event: MouseEvent) {
    emit('open', version)
    popover.value?.toggle(event)
}
</script>

<template>
    <div class="gap-1 flex items-center">
        <Button label="Versions" size="small" text severity="secondary" @click="popover?.toggle($event)">
            <template #icon><Icon icon="heroicons:clock" class="mr-1 text-base" /></template>
        </Button>

        <span v-if="openVersion" class="text-surface-500 text-xs font-medium">v{{ openVersion.version_number }}</span>

        <Icon
            v-if="openVersion?.is_primary"
            icon="heroicons:bookmark-solid"
            class="text-primary-500 text-sm"
            aria-label="Primary version"
        />
    </div>

    <Popover ref="popover">
        <div class="w-72 text-sm">
            <ul class="max-h-[50vh] overflow-y-auto">
                <li v-for="version in versions" :key="version.id">
                    <div
                        class="gap-2 rounded px-2 py-1.5 hover:bg-surface-100 dark:hover:bg-surface-800 flex items-center"
                        :class="{ 'bg-surface-100 dark:bg-surface-800': version.id === openVersionId }"
                    >
                        <button
                            type="button"
                            class="gap-2 min-w-0 flex flex-1 items-center text-left"
                            @click="open(version, $event)"
                        >
                            <span class="text-surface-500 w-8 shrink-0 text-xs">v{{ version.version_number }}</span>
                            <span class="min-w-0 flex-1 truncate">{{ version.label ?? 'Untitled' }}</span>
                            <span
                                v-if="isDirty(version)"
                                class="rounded-full size-1.5 bg-amber-500 shrink-0"
                                aria-label="Unsaved changes"
                            />
                            <Icon
                                v-if="version.is_primary"
                                icon="heroicons:bookmark-solid"
                                class="text-primary-500 shrink-0 text-sm"
                                aria-label="Primary version"
                            />
                        </button>

                        <template v-if="editable">
                            <Button
                                v-if="!version.is_primary"
                                size="small"
                                text
                                severity="secondary"
                                title="Set as primary"
                                aria-label="Set as primary"
                                :disabled="isBusy"
                                @click="emit('set-primary', version)"
                            >
                                <template #icon><Icon icon="heroicons:bookmark" class="text-sm" /></template>
                            </Button>

                            <Button
                                size="small"
                                text
                                severity="danger"
                                title="Delete version"
                                aria-label="Delete version"
                                :disabled="isBusy"
                                @click="emit('delete', version)"
                            >
                                <template #icon><Icon icon="heroicons:trash" class="text-sm" /></template>
                            </Button>
                        </template>
                    </div>
                </li>
            </ul>

            <div v-if="editable" class="gap-2 mt-1 pt-2 border-surface-200 dark:border-surface-700 flex border-t">
                <Button label="New version" size="small" text :disabled="isBusy" @click="emit('create')">
                    <template #icon><Icon icon="heroicons:plus" class="mr-1 text-sm" /></template>
                </Button>

                <Button
                    v-if="hasPinnedVersion"
                    label="Use latest as primary"
                    size="small"
                    text
                    severity="secondary"
                    :disabled="isBusy"
                    @click="emit('use-latest-as-primary')"
                />
            </div>
        </div>
    </Popover>
</template>
