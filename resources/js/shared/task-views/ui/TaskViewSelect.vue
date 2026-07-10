<script setup lang="ts">
import Select from 'primevue/select'
import { computed } from 'vue'

type TaskViewOption = {
    key: string
    label: string
}

const props = defineProps<{
    modelValue: string
    options: TaskViewOption[]
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', key: string): void
}>()

const activeLabel = computed(() => props.options.find((option) => option.key === props.modelValue)?.label ?? '')

// Strip the default Select chrome (bg/border/shadow) so the control reads as an
// outlined-text button, matching FilterButton/SortButton.
const selectPt = {
    root: { class: '!bg-transparent !border-0 !shadow-none' },
    label: { class: '!py-1 !pl-2 !pr-1 !text-sm !text-surface-600 dark:!text-surface-300' },
    dropdown: { class: '!w-6 !text-surface-500' },
}
</script>

<template>
    <Select
        :model-value="modelValue"
        :options="options"
        option-label="label"
        option-value="key"
        size="small"
        :pt="selectPt"
        @update:model-value="emit('update:modelValue', $event)"
    >
        <template #value>
            <span>View: {{ activeLabel }}</span>
        </template>
    </Select>
</template>
