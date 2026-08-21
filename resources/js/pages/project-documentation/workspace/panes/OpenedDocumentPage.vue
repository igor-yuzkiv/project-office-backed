<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import { useProjectDocumentQuery } from '@/entities/project-document'

const router = useRouter()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId')

const { projectDocument, isError, isFetching } = useProjectDocumentQuery(documentId, { with_path: true })

// A document that failed to load, or that belongs to another project, is not this
// project's document. Only the centre panel says so — the tree stays usable.
const isMissing = computed(
    () => isError.value || (!!projectDocument.value && projectDocument.value.project_id !== projectId.value)
)

function openDocumentationRoot() {
    router.push({ name: 'project-documentation', params: { projectId: projectId.value } })
}
</script>

<template>
    <div v-if="isMissing" class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
        <Icon icon="heroicons:document-magnifying-glass" class="text-surface-300 text-4xl" />
        <p class="text-surface-700 dark:text-surface-200 text-base font-medium">Document not found</p>
        <p class="text-surface-500 max-w-sm text-sm text-center">
            It was deleted or moved to another project. Pick another document on the left, or go back to the
            documentation root.
        </p>
        <Button label="Back to documentation root" size="small" severity="secondary" @click="openDocumentationRoot()" />
    </div>

    <div v-else-if="!projectDocument && isFetching" class="gap-3 p-8 flex flex-col">
        <Skeleton height="2rem" width="20rem" />
        <Skeleton v-for="n in 6" :key="n" height="1rem" />
    </div>

    <div v-else-if="projectDocument" class="gap-2 p-8 flex flex-col">
        <h1 class="text-surface-900 dark:text-surface-0 text-xl font-semibold">{{ projectDocument.title }}</h1>
        <span class="text-surface-500 text-xs">{{ projectDocument.key }}</span>
    </div>
</template>
