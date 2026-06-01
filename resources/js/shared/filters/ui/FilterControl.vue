<script setup lang="ts">
import Panel from 'primevue/panel'
import Select from 'primevue/select'
import { computed } from 'vue'
import type { AnyFilterDef } from '../types/filter-def.types'
import type { MatchMode, MatchModeOption } from '../types/match-mode.types'
import { MATCH_MODE_OPTIONS } from '../lib/filter-config'
import TextValueInput from './value-inputs/TextValueInput.vue'
import IntegerValueInput from './value-inputs/IntegerValueInput.vue'
import BooleanValueInput from './value-inputs/BooleanValueInput.vue'
import DateTimeValueInput from './value-inputs/DateTimeValueInput.vue'

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

const matchModeOptions = computed<MatchModeOption[]>(() => MATCH_MODE_OPTIONS[props.def.dataType])

const showMatchMode = computed(() => !props.def.withoutMatchMode && matchModeOptions.value.length > 0)

const showValueInput = computed(() => props.def.dataType !== 'nullable')

function onMatchModeChange(value: MatchMode | null) {
    emit('change', props.filterKey, { matchMode: value })
}

function onValueChange(value: unknown) {
    emit('change', props.filterKey, { value } as Partial<AnyFilterDef>)
}

const asString = (v: unknown): string | null => v as string | null
const asNumber = (v: unknown): number | null => v as number | null
const asBoolean = (v: unknown): boolean | null => v as boolean | null
const asDate = (v: unknown): Date | null => v as Date | null

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

            <template v-if="showValueInput">
                <TextValueInput
                    v-if="def.dataType === 'text'"
                    :model-value="asString(def.value)"
                    @update:model-value="onValueChange"
                />
                <IntegerValueInput
                    v-else-if="def.dataType === 'integer'"
                    :model-value="asNumber(def.value)"
                    @update:model-value="onValueChange"
                />
                <BooleanValueInput
                    v-else-if="def.dataType === 'boolean'"
                    :model-value="asBoolean(def.value)"
                    @update:model-value="onValueChange"
                />
                <DateTimeValueInput
                    v-else-if="def.dataType === 'datetime'"
                    :model-value="asDate(def.value)"
                    @update:model-value="onValueChange"
                />
            </template>
        </div>
    </Panel>
</template>
