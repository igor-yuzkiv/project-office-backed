<script setup lang="ts">
import { ref } from 'vue'
import Popover from 'primevue/popover'
import Button from 'primevue/button'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { storeToRefs } from 'pinia'

const authStore = useAuthStore()
const router = useRouter()
const popover = ref<InstanceType<typeof Popover>>()
const { user } = storeToRefs(authStore)

async function handleLogout() {
    await authStore.logout()
    await router.push({ name: 'login' })
}

function toggle(event: MouseEvent) {
    popover.value?.toggle(event)
}

defineExpose({ toggle })
</script>

<template>
    <Popover ref="popover">
        <div class="min-w-48 gap-3 p-1 flex flex-col">
            <div class="gap-0.5 flex flex-col">
                <span class="text-sm font-medium text-surface-900 dark:text-surface-0">
                    {{ user?.name }}
                </span>
                <span class="text-xs text-surface-500">
                    {{ user?.email }}
                </span>
            </div>

            <Button label="Sign Out" severity="secondary" size="small" fluid @click="handleLogout" />
        </div>
    </Popover>
</template>
