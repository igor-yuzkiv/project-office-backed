<script setup lang="ts">
import { watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { TagList } from '@/widgets/tags/metadata'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const documentId = route.params.id as string

const { projectDocument, isError } = useProjectDocumentQuery(documentId)

watch(isError, (error) => {
    if (error) toast.error('Failed to load document.')
})

watch(
    projectDocument,
    (doc) => {
        if (doc) layoutStore.setPageTitle(doc.title)
    },
    { immediate: true }
)

useHeaderActions([
    {
        key: 'edit-project-document',
        title: 'Edit',
        action: () => router.push({ name: 'project-document-edit', params: { id: documentId } }),
        is_primary: true,
    },
])

useBreadcrumbs(() => [
    ...(projectDocument.value?.project
        ? [
              {
                  label: projectDocument.value.project.name,
                  to: { name: 'project-details', params: { id: projectDocument.value.project.id } },
              },
          ]
        : []),
    ...(projectDocument.value?.project_id
        ? [
              {
                  label: 'Documentation',
                  to: {
                      name: 'project-details.documentation',
                      params: { id: projectDocument.value.project_id },
                  },
              },
          ]
        : []),
    { label: projectDocument.value?.title ?? 'Document' },
])
</script>

<template>
    <div v-if="projectDocument" class="gap-4 p-4 flex flex-1 flex-col overflow-auto">
        <div class="gap-2 flex flex-col">
            <h1 class="text-2xl font-semibold text-surface-900 dark:text-surface-0">
                {{ projectDocument.title }}
            </h1>
            <TagList :tags="projectDocument.tags ?? []" />
        </div>

        <MarkdownPreview v-if="projectDocument.content" :model-value="projectDocument.content" />
        <p v-else class="text-surface-400">No content yet.</p>
    </div>
</template>
