<script setup lang="ts">
import Checkbox from 'primevue/checkbox'
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

const matchModeOptions = computed<MatchModeOption[]>(() => MATCH_MODE_OPTIONS[props.def.dataType])

const showMatchMode = computed(() => !props.def.withoutMatchMode && matchModeOptions.value.length > 0)

const showValueInput = computed(() => props.def.dataType !== 'nullable')

function onEnabledChange(value: boolean) {
    emit('change', props.filterKey, { enabled: value })
}

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
</script>

<template>
    <div class="gap-2 flex flex-col">
        <div class="gap-2 flex items-center">
            <Checkbox :model-value="def.enabled" :binary="true" @update:model-value="onEnabledChange" />
            <span class="text-sm font-medium text-surface-700">{{ def.label }}</span>
        </div>

        <div v-if="def.enabled" class="gap-2 pl-6 flex flex-col">
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
    </div>
</template>
