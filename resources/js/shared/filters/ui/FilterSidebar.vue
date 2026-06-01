<script setup lang="ts">
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import type { AnyFilterDef, FilterDefMap } from '../types/filter-def.types'
import FilterGroup from './FilterGroup.vue'

const props = withDefaults(
    defineProps<{
        defMap: FilterDefMap
        title?: string
    }>(),
    {
        title: 'Filters',
    }
)

const visible = defineModel<boolean>('visible', { default: false })

const emit = defineEmits<{
    apply: []
    reset: []
    change: [key: string, patch: Partial<AnyFilterDef>]
}>()

function onApply() {
    visible.value = false
    emit('apply')
}

function onReset() {
    emit('reset')
}
</script>

<template>
    <Drawer v-model:visible="visible" :header="title" position="right" class="!w-80">
        <div class="gap-4 flex h-full flex-col">
            <div class="flex-1 overflow-y-auto">
                <FilterGroup :def-map="props.defMap" @change="(k, patch) => emit('change', k, patch)" />
            </div>

            <div class="gap-2 border-surface-200 pt-4 flex border-t">
                <Button label="Apply" class="flex-1" @click="onApply" />
                <Button label="Reset" severity="secondary" outlined class="flex-1" @click="onReset" />
            </div>
        </div>
    </Drawer>
</template>
