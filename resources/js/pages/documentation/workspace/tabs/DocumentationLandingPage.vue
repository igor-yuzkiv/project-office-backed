<script setup lang="ts">
import { computed } from 'vue'
import { useRouteParams } from '@vueuse/router'
import Button from 'primevue/button'
import { useProjectDocumentsSearchQuery } from '@/entities/project-document/queries'
import type { FilterPayloadItem } from '@/shared/filters'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'

const RECENT_DOCUMENTS_COUNT = 5

const emit = defineEmits<{
    (e: 'create-document'): void
}>()

const projectId = useRouteParams<string>('projectId')

const searchParams = computed(() => ({
    query: '',
    filters: [
        {
            filter_key: 'lookup',
            field_name: 'project_id',
            value: projectId.value,
            matchMode: null,
            params: {},
        } satisfies FilterPayloadItem,
    ],
    sort_by: 'updated_at',
    sort_order: 'desc' as const,
    page: 1,
    per_page: RECENT_DOCUMENTS_COUNT,
}))

const { projectDocuments: recentDocuments } = useProjectDocumentsSearchQuery(searchParams)
</script>

<template>
    <div class="gap-6 px-6 py-10 flex flex-1 flex-col items-center justify-center">
        <div class="gap-1 flex flex-col items-center text-center">
            <p class="type-section">Project documentation</p>
            <p class="type-meta">Pick a document in the tree, or start a new one.</p>
        </div>

        <Button label="New document" size="small" @click="emit('create-document')" />

        <div v-if="recentDocuments.length" class="max-w-md flex w-full flex-col">
            <p class="type-meta pb-1 font-medium">Recently updated</p>

            <RouterLink
                v-for="document in recentDocuments"
                :key="document.id"
                :to="{
                    name: 'project-documentation.document',
                    params: { projectId, documentId: document.id },
                }"
                class="hairline hover:bg-hover text-ink gap-3 px-2 py-1.5 flex items-baseline text-[13.5px] transition-colors"
            >
                <span class="truncate">{{ document.title }}</span>
                <span class="type-meta-3 ml-auto shrink-0 whitespace-nowrap">
                    {{ formatRelativeTime(document.updated_at) }}
                </span>
            </RouterLink>
        </div>
    </div>
</template>
