<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { useTaskQuery } from '@/entities/task/queries'
import { MarkdownPreview } from '@/shared/components/md-editor'

const taskId = useRouteParams<string>('id')

const { task } = useTaskQuery(taskId)
</script>

<template>
    <div class="md:container md:mx-auto">
        <MarkdownPreview v-if="task?.description" :model-value="task.description" show-catalog />
        <p v-else class="text-sm text-surface-400 italic">No description available.</p>
    </div>
</template>
