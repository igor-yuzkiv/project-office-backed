<script setup lang="ts">
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
    <div class="gap-6 p-10 flex flex-1 flex-col items-center justify-center">
        <Icon icon="heroicons:exclamation-triangle" class="text-surface-200 dark:text-surface-800 text-8xl" />

        <div class="gap-1.5 flex flex-col items-center text-center">
            <p class="text-surface-700 dark:text-surface-200 text-lg font-medium">Page not found</p>
            <p class="text-surface-500 max-w-sm text-sm">This address does not lead anywhere in the application.</p>
        </div>

        <Button label="Go to home" size="small" @click="router.push({ name: 'home' })" />
    </div>
</template>
