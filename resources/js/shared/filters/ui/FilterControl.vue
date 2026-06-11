<script setup lang="ts">
import Panel from 'primevue/panel'
import Select from 'primevue/select'
import { computed, markRaw } from 'vue'
import type { Component } from 'vue'
import type { AnyFilterDef, FilterDataType } from '../types/filter-def.types'
import type { MatchMode, MatchModeOption } from '../types/match-mode.types'
import { FILTER_TYPE_CONFIG } from '../lib/filter-config'
import TextInput from './value-inputs/TextInput.vue'
import IntegerInput from './value-inputs/IntegerInput.vue'
import BooleanInput from './value-inputs/BooleanInput.vue'
import DateTimeInput from './value-inputs/DateTimeInput.vue'
import SelectInput from './value-inputs/SelectInput.vue'

const DATA_TYPE_COMPONENTS: Record<FilterDataType, Component | null> = {
    text: markRaw(TextInput),
    integer: markRaw(IntegerInput),
    boolean: markRaw(BooleanInput),
    datetime: markRaw(DateTimeInput),
    nullable: null,
    lookup: null,
    select: markRaw(SelectInput),
}

const props = defineProps<{
    filterKey: string
    def: AnyFilterDef
}>()

const emit = defineEmits<{
    change: [key: string, patch: Partial<AnyFilterDef>]
}>()

// expanded = enabled: open panel → filter active, collapsed → filter disabled
const panelCollapsed = computed({
    get: () => !props.def.enabled,
    set: (val: boolean) => emit('change', props.filterKey, { enabled: !val }),
})

const matchModeOptions = computed<MatchModeOption[]>(() => FILTER_TYPE_CONFIG[props.def.dataType].matchModes)

const showMatchMode = computed(() => !props.def.withoutMatchMode && matchModeOptions.value.length > 0)

const resolvedComponent = computed<Component | null>(
    () => props.def.component ?? DATA_TYPE_COMPONENTS[props.def.dataType]
)

function onMatchModeChange(value: MatchMode | null) {
    emit('change', props.filterKey, { matchMode: value })
}

function onValueChange(value: unknown) {
    emit('change', props.filterKey, { value } as Partial<AnyFilterDef>)
}

const panelPt = {
    root: { class: '!rounded-none !shadow-none !border-0 !border-b !border-surface-200 dark:!border-surface-700' },
    header: { class: '!px-0 !py-2.5' },
    content: { class: '!px-0 !pt-0 !pb-3' },
}
</script>

<template>
    <Panel v-model:collapsed="panelCollapsed" :header="def.label" toggleable :pt="panelPt">
        <div class="gap-2 flex flex-col">
            <Select
                v-if="showMatchMode"
                :model-value="def.matchMode"
                :options="matchModeOptions"
                option-label="label"
                option-value="value"
                placeholder="Match mode"
                class="w-full"
                @update:model-value="onMatchModeChange"
            />

            <component
                :is="resolvedComponent"
                v-if="resolvedComponent"
                :model-value="def.value"
                v-bind="def.inputProps"
                @update:model-value="onValueChange"
            />
        </div>
    </Panel>
</template>
