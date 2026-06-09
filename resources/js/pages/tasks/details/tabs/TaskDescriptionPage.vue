<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useTaskQuery } from '@/entities/task/queries'
import { MarkdownPreview } from '@/shared/components/md-editor'

const route = useRoute()
const taskId = route.params.id as string

const { task } = useTaskQuery(taskId)
</script>

<template>
    <div class="p-4">
        <MarkdownPreview v-if="task?.description" :model-value="task.description" />
        <p v-else class="text-sm text-surface-400 italic">No description available.</p>
    </div>
</template>
