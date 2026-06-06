<script setup lang="ts" generic="T extends object">
import { ref, watch, type Ref } from 'vue'
import AutoComplete from 'primevue/autocomplete'

defineOptions({ inheritAttrs: false })

const props = withDefaults(
    defineProps<{
        options: T[]
        optionLabel: string | ((item: T) => string)
        loading?: boolean
        invalid?: boolean
        inputClass?: string
        placeholder?: string
        dropdown?: boolean
    }>(),
    {
        loading: false,
        invalid: false,
        dropdown: true,
    }
)

const emit = defineEmits<{
    search: [query: string]
}>()

const modelValue = defineModel<T | null>({ required: true })

const suggestions: Ref<T[]> = ref([])

watch(
    () => props.options,
    (opts) => {
        suggestions.value = opts
    }
)

function onComplete(query: string) {
    emit('search', query)
    suggestions.value = [...props.options]
}
</script>

<template>
    <AutoComplete
        v-bind="{ ...$attrs, suggestions, optionLabel, loading, invalid, inputClass, placeholder, dropdown }"
        v-model="modelValue"
        force-selection
        @complete="onComplete($event.query)"
    >
        <template v-for="(_, name) in $slots" #[name]="slotProps">
            <slot :name="name" v-bind="slotProps ?? {}" />
        </template>
    </AutoComplete>
</template>
