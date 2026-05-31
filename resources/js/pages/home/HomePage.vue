<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { IProject } from '@/entities/project/types'
import { fetchProjectsRequest } from '@/entities/project/api'
import type { PaginatedResponse } from '@/shared/types'

const projects = ref<PaginatedResponse<IProject> | null>(null)

onMounted(async () => {
    projects.value = await fetchProjectsRequest().catch((err) => {
        console.error(err)
        return null
    })
})
</script>

<template>
    <div class="flex-1 flex-col p-2 overflow-auto">
        <pre>{{ projects }}</pre>
    </div>
</template>
