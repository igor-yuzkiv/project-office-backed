<script setup lang="ts">
import { computed, ref, useTemplateRef } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import Skeleton from 'primevue/skeleton'
import type { MenuItem } from 'primevue/menuitem'
import type { ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import type { DocumentationTreeRow } from '../composables/use.documentation-tree'

// State is owned by the page, which survives the remounts this panel goes through
// when the workspace layout changes. The panel renders it and reports intent.
const props = defineProps<{
    rows: DocumentationTreeRow[]
    isPending: boolean
    isError: boolean
    selectedDocumentId?: string | null
}>()

const emit = defineEmits<{
    (e: 'select', documentId: string): void
    (e: 'toggle-node', document: ProjectDocumentTreeNodeDto): void
    (e: 'load-more', levelKey: string): void
    (e: 'create-root'): void
    (e: 'create-child', document: ProjectDocumentTreeNodeDto): void
    (e: 'delete', document: ProjectDocumentTreeNodeDto): void
    (e: 'retry'): void
}>()

const isEmpty = computed(() => props.rows.length === 0)

const nodeMenu = useTemplateRef<InstanceType<typeof Menu>>('nodeMenu')
const menuDocument = ref<ProjectDocumentTreeNodeDto | null>(null)

const menuItems = computed<MenuItem[]>(() => {
    const document = menuDocument.value

    if (!document) return []

    return [
        {
            label: 'New document inside',
            icon: 'pi pi-plus',
            disabled: !document.can_have_children,
            command: () => emit('create-child', document),
        },
        {
            label: 'Delete',
            icon: 'pi pi-trash',
            command: () => emit('delete', document),
        },
    ]
})

function openNodeMenu(event: MouseEvent, document: ProjectDocumentTreeNodeDto) {
    menuDocument.value = document
    nodeMenu.value?.toggle(event)
}
</script>

<template>
    <section class="flex h-full flex-col overflow-hidden">
        <!-- Same height as the document toolbar across the way, so the two rows line up. -->
        <header class="gap-2 px-3 py-1.5 flex items-center" style="min-height: 2.75rem">
            <h2 class="text-surface-600 dark:text-surface-300 text-xs font-semibold tracking-wide uppercase">
                Documents
            </h2>

            <Button label="Add" size="small" text severity="secondary" class="ml-auto" @click="emit('create-root')">
                <template #icon><Icon icon="material-symbols:add" class="mr-1 text-base" /></template>
            </Button>
        </header>

        <div v-if="isPending" class="gap-2 p-3 flex flex-col">
            <Skeleton v-for="n in 6" :key="n" height="1.5rem" />
        </div>

        <div v-else-if="isError" class="gap-3 p-6 flex flex-col items-center text-center">
            <Icon icon="heroicons:exclamation-triangle" class="text-2xl text-red-500" />
            <p class="text-surface-500 text-sm">Could not load the document tree.</p>
            <Button label="Try again" size="small" severity="secondary" @click="emit('retry')" />
        </div>

        <div v-else-if="isEmpty" class="gap-3 p-6 flex flex-col items-center text-center">
            <Icon icon="heroicons:document-plus" class="text-surface-300 text-3xl" />
            <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">No documents yet</p>
            <p class="text-surface-500 text-xs">Create the first document — it becomes a tree root.</p>
            <Button label="Create document" size="small" @click="emit('create-root')" />
        </div>

        <div v-else class="p-2 flex-1 overflow-auto">
            <template v-for="row in rows" :key="row.key">
                <div
                    v-if="row.kind === 'node'"
                    class="group hover:bg-surface-100 dark:hover:bg-surface-800 gap-1 pr-1 rounded-md flex items-center"
                    :class="{
                        'bg-primary-50 dark:bg-primary-900/30': row.document.id === props.selectedDocumentId,
                    }"
                    :style="{ paddingLeft: `${row.depth * 0.75}rem` }"
                >
                    <button
                        v-if="row.document.has_children"
                        type="button"
                        class="text-surface-400 hover:text-surface-600 p-1 shrink-0"
                        :aria-label="row.isExpanded ? 'Collapse' : 'Expand'"
                        @click="emit('toggle-node', row.document)"
                    >
                        <Icon
                            :icon="row.isExpanded ? 'heroicons:chevron-down' : 'heroicons:chevron-right'"
                            class="text-sm"
                        />
                    </button>
                    <span v-else class="w-6 shrink-0" />

                    <button
                        type="button"
                        class="gap-2 py-1.5 min-w-0 text-sm flex flex-1 items-center text-left"
                        :class="
                            row.document.id === props.selectedDocumentId
                                ? 'text-primary-700 dark:text-primary-300 font-medium'
                                : 'text-surface-700 dark:text-surface-200'
                        "
                        :title="row.document.title"
                        @click="emit('select', row.document.id)"
                    >
                        <Icon
                            :icon="row.document.has_children ? 'heroicons:folder' : 'heroicons:document-text'"
                            class="text-surface-400 text-base shrink-0"
                        />
                        <span class="truncate">{{ row.document.title }}</span>
                    </button>

                    <Button
                        severity="secondary"
                        text
                        rounded
                        size="small"
                        class="opacity-0 group-hover:opacity-100"
                        :aria-label="`Actions for ${row.document.title}`"
                        @click="openNodeMenu($event, row.document)"
                    >
                        <template #icon>
                            <Icon icon="heroicons:ellipsis-horizontal" class="text-base" />
                        </template>
                    </Button>
                </div>

                <div v-else class="py-1" :style="{ paddingLeft: `${row.depth * 0.75 + 1.5}rem` }">
                    <Button
                        text
                        size="small"
                        severity="secondary"
                        :loading="row.isLoading"
                        :label="`Show ${row.remaining} more…`"
                        @click="emit('load-more', row.levelKey)"
                    />
                </div>
            </template>
        </div>

        <Menu ref="nodeMenu" :model="menuItems" popup />
    </section>
</template>
