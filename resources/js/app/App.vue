<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { DefaultLayout, AuthLayout } from '@/app/shell'
import type { AppLayoutName } from '@/app/shell'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import type { ToastMessage } from '@/shared/composables'

const route = useRoute()
const themeStore = useAppThemeStore()

onMounted(() => {
    themeStore.initialize()
})

const AppLayoutComponentMap: Record<AppLayoutName, unknown> = {
    default: DefaultLayout,
    auth: AuthLayout,
}

const toastDotClass: Record<string, string> = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warn: 'bg-amber-500',
    info: 'bg-accent',
    secondary: 'bg-ink-3',
    contrast: 'bg-ink-3',
}

const layoutComponent = computed(() => {
    const layout = route.meta?.layout as AppLayoutName | undefined
    if (layout && layout in AppLayoutComponentMap) {
        return AppLayoutComponentMap[layout]
    }
    return AppLayoutComponentMap.default
})

// Pages read their record id once on setup, so moving between two records of the same route
// (pinned projects in the sidebar) must mount a fresh page; moving between its child routes must not.
const pageKey = computed(() => {
    const path = route.matched[0]?.path ?? ''
    const ownParams = [...path.matchAll(/:(\w+)/g)].map(([, name]) => String(route.params[name] ?? ''))
    return [path, ...ownParams].join('/')
})
</script>

<template>
    <component :is="layoutComponent">
        <router-view v-slot="{ Component }">
            <transition name="page" mode="out-in">
                <component :is="Component" :key="pageKey" />
            </transition>
        </router-view>
    </component>
    <!-- One dark line bottom-right: `bg-ink`/`text-page` invert with the theme on their own. -->
    <Toast
        position="bottom-right"
        :pt="{
            root: { class: 'w-auto max-w-md' },
            message: { class: 'bg-ink text-page border-ink rounded-lg shadow-lg' },
            messageContent: { class: 'gap-2 px-3.5 py-2.5 items-center' },
            closeButton: { class: 'text-page hover:bg-page/15 shrink-0' },
        }"
    >
        <template #message="{ message }: { message: ToastMessage }">
            <span class="gap-2.5 min-w-0 flex items-center text-[13px]">
                <i class="h-[7px] w-[7px] shrink-0 rounded-full" :class="toastDotClass[message.severity ?? 'info']" />
                <!-- Confirmations stay one line; an error or warning carries a server message worth reading whole. -->
                <span
                    :class="
                        message.severity === 'error' || message.severity === 'warn' ? 'whitespace-normal' : 'truncate'
                    "
                >
                    {{ message.detail ?? message.summary }}
                </span>
                <RouterLink v-if="message.link" :to="message.link.to" class="shrink-0 underline underline-offset-2">
                    {{ message.link.label }}
                </RouterLink>
            </span>
        </template>
    </Toast>
    <ConfirmDialog />
</template>
