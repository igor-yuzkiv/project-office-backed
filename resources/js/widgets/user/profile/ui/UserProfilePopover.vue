<script setup lang="ts">
import { computed, ref } from 'vue'
import Popover from 'primevue/popover'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import { getInitials } from '@/shared/utils/string.util.ts'

const props = defineProps<{
    name: string
    email: string
}>()

const emit = defineEmits<{
    logout: []
}>()

const initials = computed(() => getInitials(props.name))

const popover = ref<InstanceType<typeof Popover>>()

function toggle(event: MouseEvent) {
    popover.value?.toggle(event)
}

defineExpose({ toggle })
</script>

<template>
    <Popover ref="popover">
        <div class="min-w-52 gap-3 p-1 flex flex-col">
            <div class="gap-2 flex items-center">
                <Avatar :label="initials" shape="circle" size="large" />
                <div class="gap-1 flex flex-col overflow-hidden">
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-0 truncate">
                        {{ name }}
                    </span>
                    <span class="text-xs text-surface-500 truncate">
                        {{ email }}
                    </span>
                </div>
            </div>

            <Button label="Sign Out" severity="secondary" size="small" fluid @click="emit('logout')" />
        </div>
    </Popover>
</template>
