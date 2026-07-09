<script setup lang="ts">
import { watch } from 'vue'
import { useRouteParams } from '@vueuse/router'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { ProjectDocumentationTreeTableView, useProjectDocumentTree } from '@/widgets/project-documents/views/tree-table'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)

const tree = useProjectDocumentTree(() => projectDocument.value?.project_id ?? '', undefined, documentId)

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
    <div class="p-4 flex h-full w-full flex-col overflow-hidden">
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
</template>
