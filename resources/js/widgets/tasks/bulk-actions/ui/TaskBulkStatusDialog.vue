<script setup lang="ts">
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { taskStatusOptions } from '@/entities/task/config'
import type { TaskStatusValue } from '@/entities/task/types'

const statusOptions = taskStatusOptions()

defineProps<{
    selectedCount: number
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'apply'): void
}>()

const visible = defineModel<boolean>('visible', { required: true })
const status = defineModel<TaskStatusValue | undefined>('status')
</script>

<template>
    <Dialog v-model:visible="visible" header="Update Status" modal :style="{ width: '25rem' }">
        <div class="gap-2 flex flex-col">
            <span class="text-sm text-surface-500">{{ selectedCount }} task(s) selected</span>
            <Select
                v-model="status"
                :options="statusOptions"
                option-label="label"
                option-value="value"
                placeholder="Status"
                class="w-full"
            />
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" text @click="visible = false" />
            <Button label="Apply" :disabled="!status || isPending" :loading="isPending" @click="emit('apply')" />
        </template>
    </Dialog>
</template>
