<script setup lang="ts">
import { ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import type { LaravelValidationErrors } from '@/shared/types'
import type { ProjectDocumentPathNodeDto, ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import { ProjectDocumentationTreeTableView, useProjectDocumentTree } from '@/widgets/project-documents/views/tree-table'
import type { EntityTreeTableColumnDef } from '@/shared/components/table'

export interface ProjectDocumentMoveSelection {
    parentId: string | null
    parent: ProjectDocumentPathNodeDto | null
}

const PICKER_COLUMNS: EntityTreeTableColumnDef[] = [
    { field: 'title', header: 'Title', expander: true },
    { field: 'key', header: 'Key', style: 'width: 14rem' },
    { field: 'status', header: 'Status', style: 'width: 12rem' },
]

const props = defineProps<{
    projectId: string
    currentDocumentId: string
    validationErrors?: LaravelValidationErrors
}>()

const emit = defineEmits<{
    (e: 'select', selection: ProjectDocumentMoveSelection): void
}>()

const visible = defineModel<boolean>('visible', { default: false })

const tree = useProjectDocumentTree(() => props.projectId)

// Staged pick inside the dialog: `null` = Root, `undefined` = nothing chosen yet.
const selectedParentId = ref<string | null | undefined>(undefined)
const selectedParent = ref<ProjectDocumentPathNodeDto | null>(null)

function selectRoot() {
    selectedParentId.value = null
    selectedParent.value = null
}

function onSelectNode(node: ProjectDocumentTreeNodeDto) {
    selectedParentId.value = node.id
    selectedParent.value = { id: node.id, key: node.key, title: node.title }
}

function onPageChange(page: number) {
    tree.loadRoot(page)
}

function confirm() {
    if (selectedParentId.value === undefined) return
    emit('select', { parentId: selectedParentId.value, parent: selectedParent.value })
    visible.value = false
}

// Reload on every open (not just the first) so a document moved elsewhere in a
// previous session doesn't keep showing at its stale position in the picker.
watch(visible, (isVisible) => {
    if (isVisible) {
        tree.loadRoot()
    }
})
</script>

<template>
    <Dialog
        v-model:visible="visible"
        header="Move Document"
        modal
        :style="{ width: '62rem' }"
        :content-style="{ height: '34rem' }"
    >
        <div class="gap-3 flex h-full flex-col overflow-hidden">
            <button
                type="button"
                class="gap-2 px-3 py-2 rounded flex items-center border"
                :class="
                    selectedParentId === null
                        ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-semibold'
                        : 'border-surface-200 dark:border-surface-700 text-surface-700 dark:text-surface-300'
                "
                @click="selectRoot"
            >
                <Icon icon="heroicons:home" class="text-lg" />
                <span>Root (no parent)</span>
            </button>

            <div class="flex-1 overflow-hidden">
                <ProjectDocumentationTreeTableView
                    selectable
                    :columns="PICKER_COLUMNS"
                    :selected-id="selectedParentId ?? null"
                    :disabled-id="currentDocumentId"
                    :tree-nodes="tree.treeNodes.value"
                    :is-pending="tree.isPending.value"
                    :pagination-meta="tree.paginationMeta.value"
                    :page="tree.page.value"
                    :expanded-keys="tree.expandedKeys.value"
                    @select-node="onSelectNode"
                    @expand-node="tree.expandNode"
                    @collapse-node="tree.collapseNode"
                    @page-change="onPageChange"
                />
            </div>

            <span v-if="props.validationErrors?.parent_id" class="text-sm text-red-500">
                {{ props.validationErrors.parent_id[0] }}
            </span>
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" text @click="visible = false" />
            <Button label="Move" :disabled="selectedParentId === undefined" @click="confirm" />
        </template>
    </Dialog>
</template>
