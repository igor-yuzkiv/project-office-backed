<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { ProjectDocumentCreateDialog, useProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'
import { ProjectDocumentsTable, useProjectDocumentTree } from '@/widgets/project-documents/documents-table'

const route = useRoute()
const projectId = route.params.id as string

const tree = useProjectDocumentTree(projectId)
const createDialog = useProjectDocumentCreateDialog()

function onPageChange(page: number) {
    tree.loadRoot(page)
}

onMounted(() => {
    tree.loadRoot()
})
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-end">
                <Button
                    severity="secondary"
                    outlined
                    text
                    label="Expand All"
                    title="Expands the direct children of every root document on this page"
                    @click="tree.expandAllOnPage()"
                >
                    <template #icon>
                        <Icon icon="heroicons:arrows-pointing-out" class="text-lg" />
                    </template>
                </Button>
                <Button severity="info" outlined text label="New Document" @click="createDialog.open(projectId)">
                    <template #icon>
                        <Icon icon="material-symbols:add" class="text-lg" />
                    </template>
                </Button>
            </div>

            <div class="flex h-full w-full flex-col overflow-hidden">
                <ProjectDocumentsTable
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
        </div>

        <ProjectDocumentCreateDialog
            v-model:visible="createDialog.visible.value"
            v-model:form-data="createDialog.formData.value"
            :validation-errors="createDialog.validationErrors.value"
            :is-pending="createDialog.isPending.value"
            @submit="createDialog.submit()"
        />
    </div>
</template>
