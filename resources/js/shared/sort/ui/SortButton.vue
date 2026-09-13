<script setup lang="ts">
defineProps<{
    label?: string
}>()

const emit = defineEmits<{
    click: []
}>()
</script>

<template>
    <button type="button" class="chip" :class="{ 'chip-set': label }" @click="emit('click')">
        <i class="pi pi-sort-alt text-[12px]" />
        {{ label ?? 'Sort' }}
    </button>
</template>
