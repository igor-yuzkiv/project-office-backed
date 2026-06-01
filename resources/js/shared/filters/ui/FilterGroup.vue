<script setup lang="ts">
import type { AnyFilterDef, FilterDefMap } from '../types/filter-def.types'
import FilterControl from './FilterControl.vue'

defineProps<{
    defMap: FilterDefMap
}>()

const emit = defineEmits<{
    change: [key: string, patch: Partial<AnyFilterDef>]
}>()
</script>

<template>
    <div class="gap-4 flex flex-col">
        <FilterControl
            v-for="(def, key) in defMap"
            :key="key"
            :filter-key="key"
            :def="def"
            @change="(k, patch) => emit('change', k, patch)"
        />
    </div>
</template>
