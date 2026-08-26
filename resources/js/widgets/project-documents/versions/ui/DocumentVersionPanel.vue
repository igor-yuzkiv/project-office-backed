<script setup lang="ts">
import { computed } from 'vue'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'
import { DataPanel, type DataPanelState } from '@/shared/components/data-panel'
import DocumentVersionList from './DocumentVersionList.vue'

const props = defineProps<{
    versions: IProjectDocumentVersion[]
    openVersionId: string | null
    dirtyIds: string[]
    hasPinnedVersion: boolean
    isBusy: boolean
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'collapse'): void
    (e: 'open', version: IProjectDocumentVersion): void
    (e: 'create'): void
    (e: 'delete', version: IProjectDocumentVersion): void
    (e: 'set-primary', version: IProjectDocumentVersion): void
    (e: 'use-latest-as-primary'): void
}>()

const state = computed<DataPanelState>(() => {
    if (props.isPending) return 'pending'
    if (props.versions.length === 0) return 'empty'

    return 'ready'
})
</script>

<template>
    <!-- No width, border or surface of its own: the host renders this either as a column beside
         the editor or as the body of a drawer over it. -->
    <div class="flex h-full flex-col">
        <DataPanel
            title="Versions"
            appearance="plain"
            :state="state"
            empty-message="No versions yet. Create one to start writing."
            class="min-h-0 flex flex-1 flex-col"
        >
            <template #action>
                <Button
                    severity="secondary"
                    text
                    rounded
                    size="small"
                    aria-label="Hide versions"
                    title="Hide versions"
                    @click="emit('collapse')"
                >
                    <template #icon><Icon icon="heroicons:clock" class="text-base" /></template>
                </Button>
            </template>

            <DocumentVersionList
                editable
                class="px-2 pb-2"
                :versions="versions"
                :open-version-id="openVersionId"
                :dirty-ids="dirtyIds"
                :has-pinned-version="hasPinnedVersion"
                :is-busy="isBusy"
                @open="emit('open', $event)"
                @create="emit('create')"
                @delete="emit('delete', $event)"
                @set-primary="emit('set-primary', $event)"
                @use-latest-as-primary="emit('use-latest-as-primary')"
            />
        </DataPanel>
    </div>
</template>
