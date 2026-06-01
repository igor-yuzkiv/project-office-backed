<script setup lang="ts">
import DatePicker from 'primevue/datepicker'

const modelValue = defineModel<Date | null>({ default: null })

withDefaults(
    defineProps<{
        placeholder?: string
        disabled?: boolean
        showTime?: boolean
    }>(),
    {
        placeholder: 'Select date',
        disabled: false,
        showTime: false,
    }
)
</script>

<template>
    <DatePicker
        class="w-full"
        v-model="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :showTime="showTime"
        :showIcon="true"
        dateFormat="yy-mm-dd"
    />
</template>
