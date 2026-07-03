<script setup lang="ts">
import { Icon } from '@iconify/vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import type { ITaskOwner } from '@/entities/task-owner/types'

defineProps<{
    owners: ITaskOwner[]
    isPending?: boolean
}>()
</script>

<template>
    <DataTable :value="owners" :loading="isPending" size="small" striped-rows>
        <Column field="user.name" header="User Name" />
        <Column field="role" header="User Role / Function">
            <template #body="{ data }">
                <span class="text-surface-500">{{ data.role ?? '—' }}</span>
            </template>
        </Column>
        <Column header="Primary" style="width: 6rem; text-align: center" header-class="text-center">
            <template #body="{ data }">
                <div class="flex justify-center">
                    <Icon v-if="data.is_primary" icon="material-symbols:star" class="text-lg text-yellow-400" />
                </div>
            </template>
        </Column>

        <template #empty>
            <div class="py-4 text-sm text-surface-400 text-center">No owners assigned</div>
        </template>
    </DataTable>
</template>
