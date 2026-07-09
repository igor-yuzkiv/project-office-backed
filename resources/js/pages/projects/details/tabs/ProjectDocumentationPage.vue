<script setup lang="ts">
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { useProjectDocumentsQuery } from '@/entities/project-document'
import { TagList } from '@/widgets/tags/metadata'
import { ProjectDocumentCreateDialog, useProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'

const route = useRoute()
const projectId = route.params.id as string

const { projectDocuments, isPending } = useProjectDocumentsQuery(projectId)

const createDialog = useProjectDocumentCreateDialog()
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-end">
                <Button severity="info" outlined text label="New Document" @click="createDialog.open(projectId)">
                    <template #icon>
                        <Icon icon="material-symbols:add" class="text-lg" />
                    </template>
                </Button>
            </div>

            <div class="flex-1 overflow-auto">
                <p v-if="isPending" class="p-2 text-surface-400">Loading...</p>
                <p v-else-if="!projectDocuments.length" class="p-2 text-surface-400">No documents yet.</p>
                <ul v-else class="gap-2 flex flex-col">
                    <li v-for="doc in projectDocuments" :key="doc.id">
                        <RouterLink
                            :to="{ name: 'project-document-details', params: { id: doc.id } }"
                            class="gap-2 p-3 rounded border-surface-200 hover:border-primary-400 dark:border-surface-700 flex flex-col border"
                        >
                            <span class="font-medium text-surface-900 dark:text-surface-0">{{ doc.title }}</span>
                            <TagList :tags="doc.tags ?? []" />
                        </RouterLink>
                    </li>
                </ul>
            </div>
        </div>

        <ProjectDocumentCreateDialog
            v-model:visible="createDialog.visible.value"
            v-model:form-data="createDialog.formData.value"
            :validation-errors="createDialog.validationErrors.value"
            :is-pending="createDialog.isPending.value"
            @submit="createDialog.submit()"
        />
    </div>
</template>
