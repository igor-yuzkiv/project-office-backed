<script setup lang="ts">
import InputText from 'primevue/inputtext'

const modelValue = defineModel<string | null>({ default: null })

withDefaults(
    defineProps<{
        placeholder?: string
        disabled?: boolean
    }>(),
    {
        placeholder: 'Enter value',
        disabled: false,
    }
)
</script>

<template>
    <InputText class="w-full" v-model="modelValue" :placeholder="placeholder" :disabled="disabled" />
</template>
