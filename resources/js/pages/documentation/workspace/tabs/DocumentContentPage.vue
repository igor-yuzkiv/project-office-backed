<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { MarkdownPreview } from '@/shared/components/md-editor'
import type { IProjectDocument } from '@/entities/project-document/types'

defineProps<{
    document: IProjectDocument
}>()
</script>

<template>
    <MarkdownPreview v-if="document.content" :model-value="document.content" class="p-4" />

    <div v-else class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center">
        <Icon icon="heroicons:document" class="text-surface-300 text-4xl" />
        <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">This document has no content</p>
        <p class="text-surface-500 max-w-sm text-xs">
            It can stay a section that only holds nested documents, or you can write something in it.
        </p>
    </div>
</template>
