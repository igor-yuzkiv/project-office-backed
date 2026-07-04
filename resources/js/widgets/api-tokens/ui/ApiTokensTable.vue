<script setup lang="ts">
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import { formatDate } from '@/shared/utils/date.util'
import { useDeleteApiTokenMutation } from '@/entities/user/mutations'
import type { IApiToken } from '@/entities/user/types'

defineProps<{
    tokens: IApiToken[]
    isPending?: boolean
}>()

const { mutateWithConfirm: revokeToken } = useDeleteApiTokenMutation()
</script>

<template>
    <DataTable :value="tokens" :loading="isPending" size="small" striped-rows>
        <Column field="name" header="Name" />
        <Column header="Expires At">
            <template #body="{ data }">
                <span class="text-surface-500">{{ formatDate(data.expires_at) ?? '—' }}</span>
            </template>
        </Column>
        <Column header="" style="width: 4rem">
            <template #body="{ data }">
                <Button
                    icon="pi pi-trash"
                    text
                    rounded
                    size="small"
                    severity="danger"
                    :title="`Revoke '${data.name}'`"
                    @click="revokeToken(data.id, `Are you sure you want to revoke '${data.name}'?`)"
                />
            </template>
        </Column>

        <template #empty>
            <div class="py-4 text-sm text-surface-400 text-center">No API tokens yet</div>
        </template>
    </DataTable>
</template>
