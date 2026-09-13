<script setup lang="ts">
import { computed, ref, useTemplateRef } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import Skeleton from 'primevue/skeleton'
import type { MenuItem } from 'primevue/menuitem'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import type { ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import { ProjectDocumentStatusMap } from '@/entities/project-document/config'
import { IconButton } from '@/shared/components/button'
import { pickStatusColors, STATUS_COLORS_FALLBACK } from '@/shared/components/status-pill'
import type { DocumentationTreeRow } from '../composables/use.documentation-tree'

// State is owned by the page, which survives the remounts this panel goes through
// when the workspace layout changes. The panel renders it and reports intent.
const props = defineProps<{
    rows: DocumentationTreeRow[]
    isPending: boolean
    isError: boolean
    selectedDocumentId?: string | null
    projectName?: string
}>()

const emit = defineEmits<{
    (e: 'select', documentId: string): void
    (e: 'toggle-node', document: ProjectDocumentTreeNodeDto): void
    (e: 'load-more', levelKey: string): void
    (e: 'collapse'): void
    (e: 'expand-all'): void
    (e: 'create-root'): void
    (e: 'create-child', document: ProjectDocumentTreeNodeDto): void
    (e: 'delete', document: ProjectDocumentTreeNodeDto): void
    (e: 'retry'): void
}>()

const theme = useAppThemeStore()

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

function dotColor(document: ProjectDocumentTreeNodeDto): string {
    return pickStatusColors(ProjectDocumentStatusMap[document.status]?.colors ?? STATUS_COLORS_FALLBACK, theme.isDark)
        .fg
}

function openNodeMenu(event: MouseEvent, document: ProjectDocumentTreeNodeDto) {
    menuDocument.value = document
    nodeMenu.value?.toggle(event)
}
</script>

<template>
    <section class="bg-canvas flex h-full flex-col overflow-hidden">
        <header class="gap-1 px-2 pt-3 pb-1 flex items-center">
            <IconButton
                icon="heroicons:bars-3-bottom-left"
                aria-label="Hide the document list"
                title="Hide the document list"
                @click="emit('collapse')"
            />

            <h2 class="type-meta min-w-0 font-medium flex-1 truncate">{{ projectName ?? 'Project' }} docs</h2>

            <IconButton
                v-if="!isPending && !isError && !isEmpty"
                icon="heroicons:bars-arrow-down"
                aria-label="Expand all"
                title="Expand all"
                @click="emit('expand-all')"
            />
            <IconButton
                icon="heroicons:plus"
                aria-label="New document"
                title="New document"
                @click="emit('create-root')"
            />
        </header>

        <div v-if="isPending" class="gap-2 p-2 flex flex-col">
            <Skeleton v-for="n in 6" :key="n" height="1.5rem" />
        </div>

        <div v-else-if="isError" class="gap-2 px-4 py-6 flex flex-col items-center text-center">
            <p class="type-meta">Could not load the document tree.</p>
            <Button label="Try again" size="small" severity="secondary" @click="emit('retry')" />
        </div>

        <div v-else-if="isEmpty" class="gap-2 px-4 py-6 flex flex-col items-center text-center">
            <p class="type-meta">No documents yet.</p>
            <Button label="New document" size="small" @click="emit('create-root')" />
        </div>

        <div v-else class="px-2 pb-3 flex-1 overflow-auto">
            <template v-for="row in rows" :key="row.key">
                <div
                    v-if="row.kind === 'node'"
                    class="group hover:bg-hover text-ink gap-1 pr-1 rounded flex min-h-[26px] items-center text-[13.5px] transition-colors"
                    :class="{ 'bg-hover font-medium': row.document.id === props.selectedDocumentId }"
                    :style="{ paddingLeft: `${row.depth}rem` }"
                >
                    <button
                        v-if="row.document.has_children"
                        type="button"
                        class="text-ink-3 hover:text-ink p-1 shrink-0"
                        :aria-label="row.isExpanded ? 'Collapse' : 'Expand'"
                        @click="emit('toggle-node', row.document)"
                    >
                        <Icon
                            :icon="row.isExpanded ? 'heroicons:chevron-down' : 'heroicons:chevron-right'"
                            class="h-3 w-3"
                        />
                    </button>
                    <span v-else class="w-5 shrink-0" />

                    <button
                        type="button"
                        class="gap-2 py-1 pr-1 min-w-0 flex flex-1 items-center text-left"
                        :title="row.document.title"
                        :aria-current="row.document.id === props.selectedDocumentId ? 'page' : undefined"
                        @click="emit('select', row.document.id)"
                    >
                        <span class="truncate">{{ row.document.title }}</span>
                        <span
                            class="size-1.5 ml-auto shrink-0 rounded-full"
                            :title="ProjectDocumentStatusMap[row.document.status]?.label"
                            :style="{ backgroundColor: dotColor(row.document) }"
                        />
                    </button>

                    <IconButton
                        icon="heroicons:ellipsis-horizontal"
                        class="opacity-0 group-hover:opacity-100 focus-visible:opacity-100"
                        :aria-label="`Actions for ${row.document.title}`"
                        @click="openNodeMenu($event, row.document)"
                    />
                </div>

                <div v-else class="py-0.5" :style="{ paddingLeft: `${row.depth + 1.25}rem` }">
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
