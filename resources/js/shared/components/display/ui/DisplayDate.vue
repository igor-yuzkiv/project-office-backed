<script setup lang="ts">
defineProps<{
    date: string | Date
    label?: string
}>()
</script>

<template>
    <div v-if="label" class="gap-1 flex flex-col">
        <span class="text-xs font-medium text-surface-400 tracking-wide uppercase">{{ label }}</span>
        <span class="text-surface-700">{{ new Date(date).toLocaleDateString() }}</span>
    </div>
    <span v-else>{{ new Date(date).toLocaleDateString() }}</span>
</template>
