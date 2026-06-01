<script setup lang="ts">
import Button from 'primevue/button'

defineProps<{
    count?: number
}>()

const emit = defineEmits<{
    click: []
}>()
</script>

<template>
    <Button
        label="Filters"
        icon="pi pi-filter"
        :severity="count ? 'primary' : 'secondary'"
        :badge="count ? String(count) : undefined"
        outlined
        @click="emit('click')"
    />
</template>
