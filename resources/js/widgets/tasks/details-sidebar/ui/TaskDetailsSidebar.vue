<script setup lang="ts">
import TaskInfoPanel from './TaskInfoPanel.vue'
import type { ITask } from '@/entities/task/types'

defineProps<{ task: ITask }>()
</script>

<template>
    <aside class="gap-4 w-1/4 flex shrink-0 flex-col overflow-y-auto">
        <TaskInfoPanel :task="task" />
    </aside>
</template>
