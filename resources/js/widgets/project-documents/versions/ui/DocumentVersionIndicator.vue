<script setup lang="ts">
import { Icon } from '@iconify/vue'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'

// Which version is on screen, said next to the content rather than inside the switcher that
// changes it — the answer is wanted far more often than the change.
defineProps<{ version: IProjectDocumentVersion | null }>()
</script>

<template>
    <p v-if="version" class="gap-1 text-xs text-surface-500 flex items-center">
        v{{ version.version_number }}
        <Icon
            v-if="version.is_primary"
            icon="heroicons:bookmark-solid"
            class="text-primary-500 text-sm"
            aria-label="Primary version"
        />
    </p>
</template>
