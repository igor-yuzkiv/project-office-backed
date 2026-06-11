<script setup lang="ts">
import MultiSelect from 'primevue/multiselect'

defineProps<{
    modelValue: (string | number)[] | null
}>()

defineEmits<{
    'update:modelValue': [value: (string | number)[] | null]
}>()
</script>

<template>
    <MultiSelect
        :model-value="modelValue"
        class="w-full"
        v-bind="$attrs"
        @update:model-value="$emit('update:modelValue', $event)"
    />
</template>
