<script setup lang="ts">
import { computed, provide, reactive } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import type { TabbedSidePanel } from '@/shared/composables'
import { SIDE_TABS_CONTEXT, type SideTabDescriptor } from '../side-tabs.context'

const props = defineProps<{
    panel: TabbedSidePanel
    /** Which edge of the workspace the column sits on — the drawer opens from the same one. */
    side: 'left' | 'right'
    /** CSS length for the column and the drawer, e.g. `24rem`. */
    width: string
}>()

// The strip is wide enough for its own buttons; the drawer stays clear of it by the same amount.
const STRIP_WIDTH = '2.75rem'

// Tabs announce themselves in template order, so the strip needs no list of its own.
const tabs = reactive<SideTabDescriptor[]>([])

const active = computed(() => {
    const chosen = props.panel.activeTab.value

    return tabs.some((tab) => tab.value === chosen) ? chosen : (tabs[0]?.value ?? null)
})

const contentVisible = computed(() => !props.panel.isCollapsed.value || props.panel.isDrawerOpen.value)

provide(SIDE_TABS_CONTEXT, {
    register: (tab) => {
        tabs.push(tab)
    },
    unregister: (value) => {
        const index = tabs.findIndex((tab) => tab.value === value)

        if (index !== -1) tabs.splice(index, 1)
    },
    active,
    contentVisible,
})

const toggleLabel = computed(() => (props.panel.isCollapsed.value ? 'Show sidebar' : 'Hide sidebar'))

// Double chevrons point where the column will go: off the edge when hiding, back when showing.
const toggleIcon = computed(() => {
    const towardsEdge = props.side === 'right' ? 'heroicons:chevron-double-right' : 'heroicons:chevron-double-left'
    const fromEdge = props.side === 'right' ? 'heroicons:chevron-double-left' : 'heroicons:chevron-double-right'

    return props.panel.isCollapsed.value ? fromEdge : towardsEdge
})

function toggle() {
    if (props.panel.isCollapsed.value) props.panel.expand()
    else props.panel.collapse()
}
</script>

<template>
    <div class="min-h-0 flex shrink-0" :class="side === 'left' ? 'flex-row-reverse' : ''">
        <aside
            v-if="!panel.isCollapsed.value"
            class="border-surface-200 dark:border-surface-700 min-h-0 shrink-0 overflow-hidden"
            :class="side === 'left' ? 'border-r' : 'border-l'"
            :style="{ width }"
        >
            <slot />
        </aside>

        <!-- Always there, even with the column open, so the tabs stay reachable in one place.
             No hover behaviour: a tab opens on a click and nothing else. -->
        <div
            class="border-surface-200 dark:border-surface-700 gap-1 py-1.5 w-11 flex shrink-0 flex-col items-center"
            :class="side === 'left' ? 'border-r' : 'border-l'"
        >
            <Button
                severity="secondary"
                text
                rounded
                size="small"
                :aria-label="toggleLabel"
                :title="toggleLabel"
                @click="toggle"
            >
                <template #icon>
                    <Icon :icon="toggleIcon" class="text-base" />
                </template>
            </Button>

            <Button
                v-for="tab in tabs"
                :key="tab.value"
                severity="secondary"
                text
                rounded
                size="small"
                :class="{ 'bg-surface-100 dark:bg-surface-800 !text-primary': tab.value === active }"
                :aria-label="tab.label"
                :title="tab.label"
                @click="panel.openTab(tab.value)"
            >
                <template #icon>
                    <Icon :icon="tab.icon" class="text-base" />
                </template>
            </Button>
        </div>

        <!-- The tabs register by being rendered, so while the column is hidden and no drawer is
             open the slot still mounts — invisibly, and every tab renders nothing in it. -->
        <div v-if="panel.isCollapsed.value && !panel.isDrawerOpen.value" hidden>
            <slot />
        </div>

        <!-- Only while the column is hidden, so a tab is never mounted twice. Not modal: the
             workspace stays usable, and it stops short of the strip so the icons stay clickable.
             Not dismissable: an outside click would fire before the icon's own click and the two
             would cancel out, so the drawer closes only through the icon or by showing the column
             again. -->
        <Drawer
            v-if="panel.isCollapsed.value"
            :visible="panel.isDrawerOpen.value"
            :position="side"
            :modal="false"
            :dismissable="false"
            class="!max-w-full"
            :style="{ width, [side === 'left' ? 'marginLeft' : 'marginRight']: STRIP_WIDTH }"
            :pt="{ header: { class: 'hidden' }, content: { class: '!p-0' } }"
        >
            <div class="h-full">
                <slot />
            </div>
        </Drawer>
    </div>
</template>
