<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

const route = useRoute()
const layoutStore = useAppLayoutStore()

const taskId = route.params.id as string

onMounted(() => {
    layoutStore.setHeaderActions([
        { key: 'edit-task', title: 'Edit Task', to: { name: 'task-edit', params: { id: taskId } }, is_primary: true },
    ])
})

onUnmounted(() => {
    layoutStore.clearHeaderActions()
})
</script>

<template>
    <div class="p-6">
        <p class="text-surface-500">Task details coming soon.</p>
    </div>
</template>
