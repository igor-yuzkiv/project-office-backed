<script setup lang="ts">
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import { Icon } from '@iconify/vue'

const model = defineModel<string>({ default: '' })

withDefaults(
    defineProps<{
        placeholder?: string
    }>(),
    {
        placeholder: 'Search...',
    }
)

const emit = defineEmits<{
    submit: []
}>()
</script>

<template>
    <IconField>
        <InputIcon class="cursor-pointer" @click="emit('submit')">
            <Icon icon="heroicons:magnifying-glass" class="h-4 w-4" />
        </InputIcon>
        <InputText
            v-model="model"
            :placeholder="placeholder"
            @keydown.enter="emit('submit')"
            pt:root:class="!border-x-0 !border-t-0 !rounded-none !shadow-none !bg-transparent"
        />
    </IconField>
</template>
