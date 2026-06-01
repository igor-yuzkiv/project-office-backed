<script setup lang="ts">
import Button from 'primevue/button'

defineProps<{
    label?: string
}>()

const emit = defineEmits<{
    click: []
}>()
</script>

<template>
    <Button :label="label ?? 'Sort'" icon="pi pi-sort-alt" severity="secondary" outlined @click="emit('click')" />
</template>
