<script setup lang="ts">
import Select from 'primevue/select'
import { computed } from 'vue'

const modelValue = defineModel<boolean | null>({ default: null })

const props = withDefaults(
    defineProps<{
        trueLabel?: string
        falseLabel?: string
        placeholder?: string
        disabled?: boolean
    }>(),
    {
        trueLabel: 'Yes',
        falseLabel: 'No',
        placeholder: 'Select value',
        disabled: false,
    }
)

const options = computed(() => [
    { label: props.trueLabel, value: true },
    { label: props.falseLabel, value: false },
])
</script>

<template>
    <Select
        class="w-full"
        v-model="modelValue"
        :options="options"
        option-label="label"
        option-value="value"
        :placeholder="placeholder"
        :disabled="disabled"
        show-clear
    />
</template>
