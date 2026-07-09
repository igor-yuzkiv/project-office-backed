<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouteParams } from '@vueuse/router'
import { useProjectDocumentQuery, useProjectDocumentChildrenQuery } from '@/entities/project-document'
import { ProjectDocumentationFlatTableView } from '@/widgets/project-documents/views/flat-table'
import { PAGE_SIZE } from '@/app/config'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)

const page = ref(1)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))
const { children, paginationMeta, isPending } = useProjectDocumentChildrenQuery(
    () => projectDocument.value?.project_id ?? '',
    documentId,
    pagination
)

function onPageChange(newPage: number) {
    page.value = newPage
}
</script>

<template>
    <div class="p-4">
        <ProjectDocumentationFlatTableView
            :documents="children"
            :is-pending="isPending"
            :pagination-meta="paginationMeta"
            :page="page"
            empty-message="No child documents yet."
            @page-change="onPageChange"
        />
    </div>
</template>
