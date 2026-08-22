<script setup lang="ts">
import { computed } from 'vue'
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import { useProjectDocumentsSearchQuery } from '@/entities/project-document/queries'
import type { FilterPayloadItem } from '@/shared/filters'
import { DisplayDate } from '@/shared/components/display'

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

const hints = [
    { action: 'Open a document', where: 'click it in the tree' },
    { action: 'Nest a document', where: 'node menu → New document inside' },
    { action: 'Delete a document', where: 'node menu → Delete' },
]
</script>

<template>
    <div class="gap-6 p-10 flex flex-1 flex-col items-center justify-center">
        <Icon icon="heroicons:book-open" class="text-surface-200 dark:text-surface-800 text-8xl" />

        <div class="gap-1.5 flex flex-col items-center text-center">
            <p class="text-surface-700 dark:text-surface-200 text-lg font-medium">Project documentation</p>
            <p class="text-surface-500 max-w-sm text-sm">
                Pick a document in the tree on the left to read it here, or start a new one.
            </p>
        </div>

        <Button label="New document" size="small" @click="emit('create-document')">
            <template #icon>
                <Icon icon="material-symbols:add" class="text-base" />
            </template>
        </Button>

        <div v-if="recentDocuments.length" class="gap-2 max-w-md flex w-full flex-col">
            <p class="text-surface-400 text-xs font-semibold tracking-wide uppercase">Recently updated</p>

            <RouterLink
                v-for="document in recentDocuments"
                :key="document.id"
                :to="{
                    name: 'project-documentation.document',
                    params: { projectId, documentId: document.id },
                }"
                class="hover:bg-surface-100 dark:hover:bg-surface-800 gap-3 px-2 py-1.5 rounded-md flex items-baseline"
            >
                <span class="text-surface-700 dark:text-surface-200 text-sm truncate">{{ document.title }}</span>
                <span class="text-surface-400 text-xs ml-auto shrink-0">
                    <DisplayDate :date="document.updated_at" />
                </span>
            </RouterLink>
        </div>

        <dl class="gap-y-2 gap-x-6 text-surface-400 text-xs grid grid-cols-[auto_auto] items-baseline">
            <template v-for="hint in hints" :key="hint.action">
                <dt class="text-surface-500 dark:text-surface-300 text-right">{{ hint.action }}</dt>
                <dd>{{ hint.where }}</dd>
            </template>
        </dl>
    </div>
</template>
