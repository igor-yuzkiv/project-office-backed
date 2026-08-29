<script setup lang="ts">
import { computed, ref, useTemplateRef } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'

// The rows and their actions, with no surface of its own: the host puts this in a side panel.
defineProps<{
    versions: IProjectDocumentVersion[]
    openVersionId: string | null
    /** Adds the per-row actions and the footer. A reader gets neither. */
    editable?: boolean
    hasPinnedVersion?: boolean
    isBusy?: boolean
}>()

const emit = defineEmits<{
    (e: 'open', version: IProjectDocumentVersion): void
    (e: 'create'): void
    (e: 'edit', version: IProjectDocumentVersion): void
    (e: 'delete', version: IProjectDocumentVersion): void
    (e: 'set-primary', version: IProjectDocumentVersion): void
    (e: 'use-latest-as-primary'): void
}>()

// One menu for every row; which row it speaks for is set as it opens.
const rowMenu = useTemplateRef<InstanceType<typeof Menu>>('rowMenu')
const menuVersion = ref<IProjectDocumentVersion | null>(null)

const menuItems = computed<MenuItem[]>(() => {
    const version = menuVersion.value

    if (!version) return []

    return [
        { label: 'Edit', icon: 'pi pi-pencil', command: () => emit('edit', version) },
        { label: 'Delete', icon: 'pi pi-trash', command: () => emit('delete', version) },
    ]
})

function openRowMenu(event: MouseEvent, version: IProjectDocumentVersion) {
    menuVersion.value = version
    rowMenu.value?.toggle(event)
}
</script>

<template>
    <div class="min-h-0 text-sm flex flex-1 flex-col">
        <Menu ref="rowMenu" :model="menuItems" popup />

        <ul class="min-h-0 flex-1 overflow-y-auto">
            <li v-for="version in versions" :key="version.id">
                <div
                    class="gap-2 rounded px-2 py-1.5 hover:bg-surface-100 dark:hover:bg-surface-800 flex items-center"
                    :class="{ 'bg-surface-100 dark:bg-surface-800': version.id === openVersionId }"
                >
                    <button
                        type="button"
                        class="gap-2 min-w-0 flex flex-1 items-center text-left"
                        @click="emit('open', version)"
                    >
                        <span class="text-surface-500 w-8 text-xs shrink-0">v{{ version.version_number }}</span>
                        <span class="min-w-0 flex-1 truncate">{{ version.label ?? 'Untitled' }}</span>
                        <Icon
                            v-if="version.is_primary"
                            icon="heroicons:bookmark-solid"
                            class="text-primary-500 text-sm shrink-0"
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
                            severity="secondary"
                            :title="`Actions for version ${version.version_number}`"
                            :aria-label="`Actions for version ${version.version_number}`"
                            :disabled="isBusy"
                            @click="openRowMenu($event, version)"
                        >
                            <template #icon><Icon icon="heroicons:ellipsis-horizontal" class="text-sm" /></template>
                        </Button>
                    </template>
                </div>
            </li>
        </ul>

        <div v-if="editable" class="gap-2 mt-1 pt-2 border-surface-200 dark:border-surface-700 flex flex-wrap border-t">
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
</template>
