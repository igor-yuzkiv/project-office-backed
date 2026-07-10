<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRouteParams } from '@vueuse/router'
import { canProjectDocumentHaveChildren, useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentationTreeTableView, useProjectDocumentTree } from '@/widgets/project-documents/views/tree-table'
import Button from 'primevue/button'
import { ProjectDocumentCreateDialog, useProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)

const tree = useProjectDocumentTree(() => projectDocument.value?.project_id ?? '', undefined, documentId)

const createDialog = useProjectDocumentCreateDialog()
const canCreateSubDocument = computed(() => canProjectDocumentHaveChildren(projectDocument.value?.depth ?? Infinity))

function openCreateSubDocumentDialog() {
    if (!projectDocument.value) return

    createDialog.open(projectDocument.value.project_id, {
        id: projectDocument.value.id,
        key: projectDocument.value.key,
        title: projectDocument.value.title,
    })
}

function onPageChange(page: number) {
    tree.loadRoot(page)
}

watch(
    () => projectDocument.value?.project_id,
    (projectId) => {
        if (projectId) tree.loadRoot()
    },
    { immediate: true }
)
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="flex h-full w-full flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-end">
                <Button
                    label="Create Sub-Document"
                    size="small"
                    outlined
                    :disabled="!canCreateSubDocument"
                    @click="openCreateSubDocumentDialog"
                />
            </div>

            <ProjectDocumentationTreeTableView
                :tree-nodes="tree.treeNodes.value"
                :is-pending="tree.isPending.value"
                :pagination-meta="tree.paginationMeta.value"
                :page="tree.page.value"
                :expanded-keys="tree.expandedKeys.value"
                @expand-node="tree.expandNode"
                @collapse-node="tree.collapseNode"
                @page-change="onPageChange"
            />
        </div>

        <ProjectDocumentCreateDialog
            v-model:visible="createDialog.visible.value"
            v-model:form-data="createDialog.formData.value"
            :validation-errors="createDialog.validationErrors.value"
            :is-pending="createDialog.isPending.value"
            :parent-document="createDialog.parentDocument.value"
            @submit="createDialog.submit()"
        />
    </div>
</template>
