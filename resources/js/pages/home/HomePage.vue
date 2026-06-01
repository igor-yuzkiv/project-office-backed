<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { PaginatedResponse } from '@/shared/types'
import { fetchTasksRequest } from '@/entities/task/api'
import type { ITask } from '@/entities/task/types'

const tasks = ref<PaginatedResponse<ITask> | null>(null)

onMounted(async () => {
    tasks.value = await fetchTasksRequest().catch((err) => {
        console.error(err)
        return null
    })
})
</script>

<template>
    <div class="p-2 flex-1 flex-col overflow-auto">
        <pre>{{ tasks }}</pre>
    </div>
</template>
