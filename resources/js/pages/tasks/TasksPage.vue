<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

const layoutStore = useAppLayoutStore()

onMounted(() => {
    layoutStore.setHeaderActions([
        { key: 'add-task', title: 'Add Task', action: () => console.log('test - Add Task'), is_primary: true },
        { key: 'add-issue', title: 'Add Issue', action: () => console.log('test - Add Issue') },
    ])
})

onUnmounted(() => {
    layoutStore.clearHeaderActions()
})
</script>

<template>
    <div class="p-6">
        <h1 class="text-xl font-semibold">Tasks</h1>
    </div>
</template>
