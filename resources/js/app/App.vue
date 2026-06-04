<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { DefaultLayout, AuthLayout } from '@/app/shell'
import type { AppLayoutName } from '@/app/shell'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'

const route = useRoute()

const AppLayoutComponentMap: Record<AppLayoutName, unknown> = {
    default: DefaultLayout,
    auth: AuthLayout,
}

const layoutComponent = computed(() => {
    const layout = route.meta?.layout as AppLayoutName | undefined
    if (layout && layout in AppLayoutComponentMap) {
        return AppLayoutComponentMap[layout]
    }
    return AppLayoutComponentMap.default
})
</script>

<template>
    <component :is="layoutComponent">
        <router-view v-slot="{ Component }">
            <transition name="page" mode="out-in">
                <component :is="Component" />
            </transition>
        </router-view>
    </component>
    <Toast />
    <ConfirmDialog />
</template>

<style scoped></style>
