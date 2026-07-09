<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { MarkdownPreview } from '@/shared/components/md-editor'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)
</script>

<template>
    <div class="p-4 md:container md:mx-auto">
        <MarkdownPreview v-if="projectDocument?.content" :model-value="projectDocument.content" />
        <p v-else class="text-sm text-surface-400 italic">No content yet.</p>
    </div>
</template>
