<script setup lang="ts">
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import type { AnyFilterDef, FilterDefMap } from '../types/filter-def.types'
import FilterList from './FilterList.vue'

const props = withDefaults(
    defineProps<{
        defMap: FilterDefMap
        title?: string
    }>(),
    { title: 'Filters' }
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
</script>

<template>
    <Drawer v-model:visible="visible" :header="title" position="right" class="!w-96">
        <div class="gap-4 flex h-full flex-col">
            <div class="flex-1 overflow-y-auto">
                <FilterList :def-map="props.defMap" @change="(k, patch) => emit('change', k, patch)" />
            </div>

            <div class="gap-2 border-surface-200 pt-4 flex border-t">
                <Button label="Apply" class="flex-1" @click="onApply" />
                <Button label="Reset" severity="secondary" outlined class="flex-1" @click="emit('reset')" />
            </div>
        </div>
    </Drawer>
</template>
