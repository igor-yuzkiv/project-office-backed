<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouteParams } from '@vueuse/router'
import Paginator from 'primevue/paginator'
import { useTaskProjectDocumentsQuery } from '@/entities/task/queries'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { CopyToClipboard } from '@/shared/components/display'
import { PAGE_SIZE } from '@/app/config'

const taskId = useRouteParams<string>('id')

const page = ref(1)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { projectDocuments, paginationMeta, isPending } = useTaskProjectDocumentsQuery(taskId, pagination)

const showPaginator = computed(() => paginationMeta.value && paginationMeta.value.last_page > 1)

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
}
</script>

<template>
    <div class="gap-3 p-4 flex flex-col">
        <div v-if="isPending" class="text-surface-400 text-sm">Loading related documents...</div>

        <div v-else-if="projectDocuments.length === 0" class="text-surface-400 text-sm">No related documents yet.</div>

        <div v-else class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y">
            <RouterLink
                v-for="doc in projectDocuments"
                :key="doc.id"
                :to="{ name: 'project-document-details', params: { id: doc.id } }"
                class="gap-3 py-2 hover:bg-surface-50 dark:hover:bg-surface-800 flex items-center"
            >
                <CopyToClipboard :text="doc.key" hide-copy-icon class="text-surface-500" />
                <span class="app-link">{{ doc.title }}</span>
                <ProjectDocumentStatusTag :status="doc.status" class="ml-auto w-fit" />
            </RouterLink>
        </div>

        <Paginator
            v-if="showPaginator"
            :rows="PAGE_SIZE"
            :total-records="paginationMeta!.total"
            :first="(page - 1) * PAGE_SIZE"
            @page="onPageChange"
        />
    </div>
</template>
