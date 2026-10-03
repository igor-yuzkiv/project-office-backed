<script setup lang="ts">
import LoadingOverlay from '@/shared/components/loading/LoadingOverlay.vue'
import { useLoadingStateStore } from '@/app/stores/use.loading-state.store'

const loadingStore = useLoadingStateStore()
</script>

<template>
    <div class="bg-page relative flex h-screen w-full items-center justify-center">
        <slot />
        <LoadingOverlay
            v-if="loadingStore.isLoading"
            :title="loadingStore.currentLoader?.title"
            :subtitle="loadingStore.currentLoader?.subtitle"
        />
    </div>
</template>
