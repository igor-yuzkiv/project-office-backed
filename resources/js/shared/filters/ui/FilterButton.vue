<script setup lang="ts">
defineProps<{
    count?: number
}>()

const emit = defineEmits<{
    click: []
}>()
</script>

<template>
    <button type="button" class="chip" :class="{ 'chip-set': count }" @click="emit('click')">
        <i class="pi pi-filter text-[12px]" />
        Filters
        <b v-if="count">{{ count }}</b>
    </button>
</template>
