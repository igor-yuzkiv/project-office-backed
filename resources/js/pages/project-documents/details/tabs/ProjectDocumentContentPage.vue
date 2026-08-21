<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { DocumentContentTab } from '@/widgets/project-documents/document-body'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)
</script>

<template>
    <div class="p-4 md:container md:mx-auto">
        <DocumentContentTab :content="projectDocument?.content ?? null" />
    </div>
</template>
