<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { DocumentCommentsTab } from '@/widgets/project-documents/document-body'

const documentId = useRouteParams<string>('id')
</script>

<template>
    <div class="app-content-background">
        <DocumentCommentsTab :document-id="documentId" />
    </div>
</template>
