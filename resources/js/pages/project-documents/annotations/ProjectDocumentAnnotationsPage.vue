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
    <div class="min-h-0 flex flex-1 overflow-hidden">
        <Skeleton v-if="isPending" class="m-6" height="20rem" />
        <p v-else-if="isError" class="p-6 text-sm text-red-500">Failed to load document.</p>
        <DocumentAnnotationMode
            v-else-if="projectDocument?.content"
            :document-id="documentId"
            :content="projectDocument.content"
        />
        <p v-else-if="projectDocument" class="p-6 text-sm text-surface-400 italic">No content yet.</p>
    </div>
</template>
