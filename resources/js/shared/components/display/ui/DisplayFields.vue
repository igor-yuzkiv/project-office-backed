<script setup lang="ts" generic="T extends object">
import { computed } from 'vue'
import { get as lodashGet } from 'lodash'
import DisplayField from './DisplayField.vue'

export type DisplayFieldConfig<TItem> = {
    name: string
    label: string
    value?: string | ((item: TItem) => unknown)
}

const props = withDefaults(
    defineProps<{
        item: T
        fields: DisplayFieldConfig<T>[]
        inline?: boolean
    }>(),
    { inline: true }
)

defineSlots<Record<string, (props: { item: T; value: unknown; field: DisplayFieldConfig<T> }) => unknown>>()

function resolveRawValue(field: DisplayFieldConfig<T>, item: T): unknown {
    if (typeof field.value === 'function') return field.value(item)
    if (typeof field.value === 'string') return lodashGet(item, field.value)
    return lodashGet(item, field.name)
}

const resolvedFields = computed(() =>
    props.fields.map((field) => {
        const rawValue = resolveRawValue(field, props.item)
        return {
            field,
            rawValue,
            displayValue: rawValue == null ? null : String(rawValue),
        }
    })
)
</script>

<template>
    <div class="md:grid-cols-2 gap-x-4 gap-y-2 grid grid-cols-1">
        <template v-for="{ field, displayValue, rawValue } in resolvedFields" :key="field.name">
            <DisplayField :label="field.label" :inline="inline" :value="displayValue">
                <template v-if="$slots[`field:${field.name}:label`]" #label>
                    <slot :name="`field:${field.name}:label`" :item="props.item" :value="rawValue" :field="field" />
                </template>
                <template v-if="$slots[`field:${field.name}:value`]" #default>
                    <slot :name="`field:${field.name}:value`" :item="props.item" :value="rawValue" :field="field" />
                </template>
            </DisplayField>
        </template>
    </div>
</template>
