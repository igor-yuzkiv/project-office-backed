<script setup lang="ts">
import { computed, ref, useTemplateRef, watch } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import Skeleton from 'primevue/skeleton'
import type { MenuItem } from 'primevue/menuitem'
import type { ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import type { useDocumentationTree } from '../composables/use.documentation-tree'

// The tree state is owned by the page: this panel is remounted whenever the
// workspace layout changes, and a remount must not throw away loaded levels.
const props = defineProps<{
    tree: ReturnType<typeof useDocumentationTree>
    selectedDocumentId?: string | null
    ancestorIds?: string[]
}>()

const emit = defineEmits<{
    (e: 'select', documentId: string): void
}>()

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
            command: () => props.tree.createChildDocument(document),
        },
        {
            label: 'Delete',
            icon: 'pi pi-trash',
            command: () => props.tree.deleteNodeDocument(document),
        },
    ]
})

function openNodeMenu(event: MouseEvent, document: ProjectDocumentTreeNodeDto) {
    menuDocument.value = document
    nodeMenu.value?.toggle(event)
}

watch(
    () => props.ancestorIds,
    (ancestorIds) => {
        if (ancestorIds?.length) props.tree.expandAncestors(ancestorIds)
    },
    { immediate: true }
)
</script>

<template>
    <section class="flex h-full flex-col overflow-hidden">
        <header
            class="border-surface-200 dark:border-surface-700 gap-2 px-3 py-2 flex items-center justify-between border-b"
        >
            <h2 class="text-surface-600 dark:text-surface-300 text-xs font-semibold tracking-wide uppercase">
                Documents
            </h2>

            <Button
                severity="secondary"
                text
                rounded
                size="small"
                title="New document"
                aria-label="New document"
                @click="tree.createRootDocument()"
            >
                <template #icon>
                    <Icon icon="material-symbols:add" class="text-base" />
                </template>
            </Button>
        </header>

        <div v-if="tree.isPending.value" class="gap-2 p-3 flex flex-col">
            <Skeleton v-for="n in 6" :key="n" height="1.5rem" />
        </div>

        <div v-else-if="tree.isError.value" class="gap-3 p-6 flex flex-col items-center text-center">
            <Icon icon="heroicons:exclamation-triangle" class="text-2xl text-red-500" />
            <p class="text-surface-500 text-sm">Could not load the document tree.</p>
            <Button label="Try again" size="small" severity="secondary" @click="tree.load()" />
        </div>

        <div v-else-if="tree.isEmpty.value" class="gap-3 p-6 flex flex-col items-center text-center">
            <Icon icon="heroicons:document-plus" class="text-surface-300 text-3xl" />
            <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">No documents yet</p>
            <p class="text-surface-500 text-xs">Create the first document — it becomes a tree root.</p>
            <Button label="Create document" size="small" @click="tree.createRootDocument()" />
        </div>

        <div v-else class="p-2 flex-1 overflow-auto">
            <template v-for="row in tree.rows.value" :key="row.key">
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
                        @click="tree.toggleNode(row.document)"
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
                        @click="tree.loadMore(row.levelKey)"
                    />
                </div>
            </template>
        </div>

        <Menu ref="nodeMenu" :model="menuItems" popup />
    </section>
</template>
