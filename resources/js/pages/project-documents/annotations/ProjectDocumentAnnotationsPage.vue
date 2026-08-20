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
    <div class="p-4 md:container md:mx-auto">
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
