<script setup lang="ts">
import { computed, provide, reactive, ref } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import type { TabbedSidePanel } from '@/shared/composables'
import { SIDE_TABS_CONTEXT, type SideTabDescriptor } from '../side-tabs.context'

const props = defineProps<{
    panel: TabbedSidePanel
    /** Which edge of the workspace the column sits on — the drawer opens from the same one. */
    side: 'left' | 'right'
    /** CSS length for the column and the drawer, e.g. `24rem`. */
    width: string
}>()

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

const stripRef = ref<HTMLElement>()
const drawerRef = ref<HTMLElement>()

// A click anywhere else dismisses the drawer. The strip is excluded because its icons have
// their own answer to a click — switching or closing the tab — and must not be pre-empted.
onClickOutside(drawerRef, props.panel.closeDrawer, { ignore: [stripRef] })
</script>

<template>
    <div class="min-h-0 flex shrink-0 relative" :class="side === 'left' ? 'flex-row-reverse' : ''">
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
            ref="stripRef"
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

        <!-- Only while the column is hidden, so a tab is never mounted twice. It lives inside this
             row, beside the strip, rather than over the whole viewport: the tabs belong to the
             workspace, not to the page. -->
        <div
            v-if="panel.isCollapsed.value && panel.isDrawerOpen.value"
            ref="drawerRef"
            class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 absolute inset-y-0 z-10 overflow-hidden shadow-lg"
            :class="side === 'left' ? 'left-11 border-r' : 'right-11 border-l'"
            :style="{ width }"
        >
            <slot />
        </div>
    </div>
</template>
