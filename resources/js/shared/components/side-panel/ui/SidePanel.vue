<script setup lang="ts">
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import type { CollapsibleSidePanel } from '@/shared/composables'

defineProps<{
    panel: CollapsibleSidePanel
    /** Which edge of the workspace the column sits on — the drawer opens from the same one. */
    side: 'left' | 'right'
    /** CSS length for the column and the drawer, e.g. `24rem`. */
    width: string
    icon: string
    /** Names the strip's button, for a reader who cannot see where it points. */
    showLabel: string
}>()
</script>

<template>
    <aside
        v-if="!panel.isCollapsed.value"
        class="border-surface-200 dark:border-surface-700 shrink-0 overflow-hidden"
        :class="side === 'left' ? 'border-r' : 'border-l'"
        :style="{ width }"
    >
        <slot :collapse="panel.collapse" />
    </aside>

    <!-- What is left of the column: a full-height strip holding the same control in the same
         place. Hovering it peeks at the panel, clicking brings the column back. -->
    <div
        v-else
        class="border-surface-200 dark:border-surface-700 py-1.5 w-11 flex shrink-0 flex-col items-center"
        :class="side === 'left' ? 'border-r' : 'border-l'"
        @mouseenter="panel.openDrawer"
    >
        <Button
            severity="secondary"
            text
            rounded
            size="small"
            :aria-label="showLabel"
            :title="showLabel"
            @click="panel.dock"
        >
            <template #icon>
                <Icon :icon="icon" class="text-base" />
            </template>
        </Button>
    </div>

    <!-- Only while the column is hidden, so the panel is never mounted twice. A peek rather than
         a mode: no mask over the workspace, and it leaves when the pointer does. The panel's own
         header button docks it here instead of hiding what is already hidden. -->
    <Drawer
        v-if="panel.isCollapsed.value"
        :visible="panel.isDrawerOpen.value"
        @update:visible="(open: boolean) => (open ? panel.openDrawer() : panel.closeDrawer())"
        :position="side"
        :modal="false"
        class="!max-w-full"
        :style="{ width }"
        :pt="{ header: { class: 'hidden' }, content: { class: '!p-0' } }"
    >
        <div class="h-full" @mouseleave="panel.closeDrawer">
            <slot :collapse="panel.dock" />
        </div>
    </Drawer>
</template>
