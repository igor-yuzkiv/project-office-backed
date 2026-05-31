<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { AppLayoutComponentMap } from '@/app/layouts'
import type { AppLayoutName } from '@/app/layouts'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'

const route = useRoute()

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
        <router-view />
    </component>
    <Toast />
    <ConfirmDialog />
</template>

<style scoped></style>
