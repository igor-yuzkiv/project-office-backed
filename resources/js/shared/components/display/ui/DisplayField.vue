<script setup lang="ts">
defineProps<{
    label: string
    value?: string | null
}>()
</script>

<template>
    <div class="gap-1 flex flex-col">
        <span class="text-xs font-medium text-surface-400 tracking-wide uppercase">{{ label }}</span>
        <span class="text-surface-700">{{ value ?? '—' }}</span>
    </div>
</template>
