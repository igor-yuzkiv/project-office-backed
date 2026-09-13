<script setup lang="ts">
import type { IProjectDocumentVersion } from '@/entities/project-document/types'
import DocumentVersionList from './DocumentVersionList.vue'

defineProps<{
    versions: IProjectDocumentVersion[]
    openVersionId: string | null
    hasPinnedVersion: boolean
    isBusy: boolean
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'open', version: IProjectDocumentVersion): void
    (e: 'create'): void
    (e: 'edit', version: IProjectDocumentVersion): void
    (e: 'delete', version: IProjectDocumentVersion): void
    (e: 'set-primary', version: IProjectDocumentVersion): void
    (e: 'use-latest-as-primary'): void
}>()
</script>

<template>
    <!-- No width, border or surface of its own, and nothing that hides it: the host decides where
         this list lives and how it goes away. -->
    <div class="bg-page flex h-full flex-col">
        <header class="gap-2 h-11 px-4 hairline flex shrink-0 items-center">
            <h2 class="type-meta">Versions</h2>
            <span v-if="!isPending" class="type-meta-3 ml-auto tabular-nums">{{ versions.length }}</span>
        </header>

        <p v-if="isPending" class="type-meta-3 px-4 py-3">Loading versions…</p>

        <p v-else-if="versions.length === 0" class="type-meta-3 px-4 py-3">No versions yet.</p>

        <DocumentVersionList
            v-else
            :versions="versions"
            :open-version-id="openVersionId"
            :has-pinned-version="hasPinnedVersion"
            :is-busy="isBusy"
            @open="emit('open', $event)"
            @create="emit('create')"
            @edit="emit('edit', $event)"
            @delete="emit('delete', $event)"
            @set-primary="emit('set-primary', $event)"
            @use-latest-as-primary="emit('use-latest-as-primary')"
        />
    </div>
</template>
