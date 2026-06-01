<script setup lang="ts">
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import Button from 'primevue/button'
import type { SortDirection, SortFieldDef } from '../types/sort.types'

const directionOptions: Array<{ value: SortDirection; label: string }> = [
    { value: 'asc', label: 'Asc' },
    { value: 'desc', label: 'Desc' },
]

defineProps<{
    visible: boolean
    fields: SortFieldDef[]
    sortBy: string
    sortOrder: SortDirection
}>()

const emit = defineEmits<{
    'update:visible': [boolean]
    'update:sortBy': [string]
    'update:sortOrder': [SortDirection]
    apply: []
}>()
</script>

<template>
    <Dialog
        :visible="visible"
        header="Sort By"
        modal
        :style="{ width: '25rem' }"
        @update:visible="emit('update:visible', $event)"
    >
        <div class="gap-2 flex items-center">
            <Select
                :model-value="sortBy"
                :options="fields"
                option-label="label"
                option-value="field"
                class="flex-1"
                @update:model-value="emit('update:sortBy', $event)"
            />
            <Select
                :model-value="sortOrder"
                :options="directionOptions"
                option-label="label"
                option-value="value"
                class="w-32"
                @update:model-value="emit('update:sortOrder', $event)"
            />
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" text @click="emit('update:visible', false)" />
            <Button label="Apply" @click="emit('apply')" />
        </template>
    </Dialog>
</template>
