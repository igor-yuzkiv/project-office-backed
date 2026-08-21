<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { DocumentRelatedTasksTab } from '@/widgets/project-documents/document-body'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)
</script>

<template>
    <div class="flex h-full w-full flex-1 flex-col overflow-hidden">
        <DocumentRelatedTasksTab
            v-if="projectDocument"
            :document-id="projectDocument.id"
            :project-id="projectDocument.project_id"
        />
    </div>
</template>
