<script setup lang="ts">
import { useRoute } from 'vue-router'

const route = useRoute()
const projectId = route.params.id as string
</script>

<template>
    <div class="p-6">
        <p class="text-surface-500">Project: {{ projectId }}</p>
    </div>
</template>
