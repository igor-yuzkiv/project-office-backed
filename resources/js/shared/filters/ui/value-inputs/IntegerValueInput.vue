<script setup lang="ts">
import InputNumber from 'primevue/inputnumber'

const modelValue = defineModel<number | null>({ default: null })

withDefaults(
    defineProps<{
        placeholder?: string
        disabled?: boolean
        min?: number
        max?: number
    }>(),
    {
        placeholder: 'Enter number',
        disabled: false,
    }
)
</script>

<template>
    <InputNumber
        class="w-full"
        v-model="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :min="min"
        :max="max"
        :useGrouping="false"
    />
</template>
