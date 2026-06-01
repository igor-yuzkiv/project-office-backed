<script setup lang="ts">
defineProps<{
    date: string | Date
}>()
</script>

<template>
    <span>{{ new Date(date).toLocaleDateString() }}</span>
</template>
