<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams, useRouteQuery } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { DocumentBody } from '@/widgets/project-documents/document-body'

const router = useRouter()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId')

// The tab lives in the URL: the details panel sits in the parent route and switches
// it from there, and moving to another document drops the query along with it.
const activeTab = useRouteQuery<string>('tab', 'document')

const { projectDocument, isError, isFetching, refetch } = useProjectDocumentQuery(documentId, { with_path: true })

// Failing to load and belonging to another project are different problems, and
// telling the user the document was deleted when the request merely failed is a lie.
const belongsElsewhere = computed(() => !!projectDocument.value && projectDocument.value.project_id !== projectId.value)

const openedDocument = computed(() => (belongsElsewhere.value ? undefined : projectDocument.value))

function openDocumentationRoot() {
    router.push({ name: 'project-documentation', params: { projectId: projectId.value } })
}

function openDocument(id: string) {
    router.push({ name: 'project-documentation.document', params: { projectId: projectId.value, documentId: id } })
}
</script>

<template>
    <div v-if="belongsElsewhere" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
        <Icon icon="heroicons:document-magnifying-glass" class="text-surface-300 text-4xl" />
        <p class="text-surface-700 dark:text-surface-200 text-base font-medium">Document not found</p>
        <p class="text-surface-500 max-w-sm text-sm text-center">
            It was deleted or moved to another project. Pick another document on the left, or go back to the
            documentation root.
        </p>
        <Button label="Back to documentation root" size="small" severity="secondary" @click="openDocumentationRoot()" />
    </div>

    <div v-else-if="isError" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
        <Icon icon="heroicons:exclamation-triangle" class="text-2xl text-red-500" />
        <p class="text-surface-700 dark:text-surface-200 text-base font-medium">Could not load the document</p>
        <p class="text-surface-500 max-w-sm text-sm text-center">
            Check your connection and try again. The document tree stays available.
        </p>
        <Button label="Try again" size="small" severity="secondary" @click="refetch()" />
    </div>

    <div v-else-if="!openedDocument && isFetching" class="gap-3 p-8 flex flex-col">
        <Skeleton height="2rem" width="20rem" />
        <Skeleton v-for="n in 6" :key="n" height="1rem" />
    </div>

    <DocumentBody
        v-else-if="openedDocument"
        v-model:tab="activeTab"
        :document="openedDocument"
        @open-document="openDocument"
    />
</template>
