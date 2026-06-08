<script setup lang="ts" generic="T extends object">
import { computed, ref, watch } from 'vue'
import AutoComplete from 'primevue/autocomplete'

defineOptions({ inheritAttrs: false })

const props = withDefaults(
    defineProps<{
        options: T[]
        optionLabel: string | ((item: T) => string)
        optionKey?: string
        object?: boolean
        loading?: boolean
        invalid?: boolean
        inputClass?: string
        placeholder?: string
        dropdown?: boolean
    }>(),
    {
        optionKey: 'id',
        object: false,
        loading: false,
        invalid: false,
        dropdown: true,
    }
)

const emit = defineEmits<{
    search: [query: string]
}>()

const modelValue = defineModel<T | string | number | null>({ required: true })

const suggestions = ref<T[]>([])

watch(
    () => props.options,
    (opts) => {
        suggestions.value = opts
    }
)

const autocompleteValue = computed<T | null>({
    get() {
        const val = modelValue.value
        if (val === null || typeof val === 'object') return val as T | null
        return props.options.find((o) => (o as Record<string, unknown>)[props.optionKey] === val) ?? null
    },
    set(item: T | null) {
        if (item === null) {
            modelValue.value = null
        } else if (props.object) {
            modelValue.value = item
        } else {
            modelValue.value = (item as Record<string, unknown>)[props.optionKey] as string | number
        }
    },
})

function onComplete(query: string) {
    emit('search', query)
    suggestions.value = [...props.options]
}
</script>

<template>
    <AutoComplete
        v-bind="{ ...$attrs, suggestions, optionLabel, loading, invalid, inputClass, placeholder, dropdown }"
        v-model="autocompleteValue"
        force-selection
        @complete="onComplete($event.query)"
    >
        <template v-for="(_, name) in $slots" #[name]="slotProps">
            <slot :name="name" v-bind="slotProps ?? {}" />
        </template>
    </AutoComplete>
</template>
