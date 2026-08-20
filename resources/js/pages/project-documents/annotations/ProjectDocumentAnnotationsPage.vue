<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { useRouter } from 'vue-router'
import Skeleton from 'primevue/skeleton'
import { useBreadcrumbs, useHeaderActions } from '@/app/shell'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { DocumentAnnotationMode } from '@/widgets/project-documents/annotation-mode'

const documentId = useRouteParams<string>('id')
const router = useRouter()

const { projectDocument, isPending, isError } = useProjectDocumentQuery(documentId)

function backToDocument() {
    router.push({ name: 'project-document-details.content', params: { id: documentId.value } })
}

useHeaderActions([{ key: 'back-to-document', title: 'Back to document', action: backToDocument }])

useBreadcrumbs(() => [
    ...(projectDocument.value?.project
        ? [
              {
                  label: projectDocument.value.project.name,
                  to: { name: 'project-details', params: { id: projectDocument.value.project.id } },
              },
          ]
        : []),
    {
        label: projectDocument.value?.title ?? 'Document',
        to: { name: 'project-document-details', params: { id: documentId.value } },
    },
    { label: 'Annotation Mode' },
])
</script>

<template>
    <!-- The layout hands the page a fixed-height, overflow-hidden slot, so the scroll lives here. -->
    <div class="p-6 annotation-canvas flex-1 overflow-y-auto">
        <Skeleton v-if="isPending" height="20rem" />
        <p v-else-if="isError" class="text-sm text-red-500">Failed to load document.</p>
        <DocumentAnnotationMode
            v-else-if="projectDocument?.content"
            :document-id="documentId"
            :content="projectDocument.content"
        />
        <p v-else-if="projectDocument" class="text-sm text-surface-400 italic">No content yet.</p>
    </div>
</template>

<style scoped>
/* The document reads as a sheet, so the surface behind it is a drafting canvas rather than a page. */
.annotation-canvas {
    background-color: var(--p-surface-100);
    background-image: radial-gradient(circle, var(--p-surface-300) 1px, transparent 1px);
    background-size: 18px 18px;
}

:global(.dark) .annotation-canvas {
    background-color: var(--p-surface-950);
    background-image: radial-gradient(circle, var(--p-surface-800) 1px, transparent 1px);
}
</style>
