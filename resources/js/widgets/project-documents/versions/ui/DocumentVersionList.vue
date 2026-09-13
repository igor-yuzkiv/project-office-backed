<script setup lang="ts">
import { computed, ref, useTemplateRef } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'
import { formatDate } from '@/shared/utils/date.util'

// The rows and their actions, with no surface of its own: the host puts this in a side panel.
defineProps<{
    versions: IProjectDocumentVersion[]
    openVersionId: string | null
    hasPinnedVersion: boolean
    isBusy: boolean
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

function subline(version: IProjectDocumentVersion): string {
    const date = formatDate(version.created_at, 'MMM d') ?? ''

    return version.author ? `${date} · ${version.author.name}` : date
}
</script>

<template>
    <div class="min-h-0 flex flex-1 flex-col text-[13px]">
        <Menu ref="rowMenu" :model="menuItems" popup />

        <ul class="p-2 min-h-0 flex-1 overflow-y-auto">
            <li v-for="version in versions" :key="version.id">
                <div
                    class="gap-x-2.5 pr-1 pl-2 hover:bg-hover text-ink rounded-md grid grid-cols-[8px_1fr_auto] items-center"
                    :class="{ 'bg-hover': version.id === openVersionId }"
                >
                    <!-- The dot column is always there so labels line up; only the primary
                         version fills it. -->
                    <span
                        class="size-2 rounded-full"
                        :class="{ 'bg-accent': version.is_primary }"
                        :aria-label="version.is_primary ? 'Primary version' : undefined"
                        :role="version.is_primary ? 'img' : undefined"
                    />

                    <button
                        type="button"
                        class="py-1.5 min-w-0 cursor-pointer text-left"
                        :class="{ 'font-medium': version.id === openVersionId }"
                        @click="emit('open', version)"
                    >
                        <span class="block truncate leading-[1.35]">
                            v{{ version.version_number }} — {{ version.label ?? 'Untitled' }}
                        </span>
                        <small class="type-meta-3 mt-0.5 font-normal block">{{ subline(version) }}</small>
                    </button>

                    <div class="flex items-center">
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
                    </div>
                </div>
            </li>
        </ul>

        <div class="gap-1 p-2 border-line flex shrink-0 flex-wrap border-t">
            <button
                type="button"
                class="type-meta px-2 py-1.5 hover:bg-hover hover:text-ink rounded-md cursor-pointer disabled:cursor-default disabled:opacity-50"
                :disabled="isBusy"
                @click="emit('create')"
            >
                New version
            </button>

            <button
                v-if="hasPinnedVersion"
                type="button"
                class="type-meta px-2 py-1.5 hover:bg-hover hover:text-ink rounded-md cursor-pointer disabled:cursor-default disabled:opacity-50"
                :disabled="isBusy"
                @click="emit('use-latest-as-primary')"
            >
                Use latest as primary
            </button>
        </div>
    </div>
</template>
