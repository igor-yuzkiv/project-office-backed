<script setup lang="ts">
import { useAuthStore } from '@/stores/auth.store'

const authStore = useAuthStore()
</script>

<template>
    <pre>{{ authStore.user }}</pre>
</template>
