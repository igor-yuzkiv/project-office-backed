import { type MaybeRefOrGetter, onUnmounted, toValue, watchEffect } from 'vue'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import type { HeaderAction } from '@/app/shell/types'

export function useHeaderActions(actions: MaybeRefOrGetter<HeaderAction[]>) {
    const layoutStore = useAppLayoutStore()

    watchEffect(() => layoutStore.setHeaderActions(toValue(actions)))
    onUnmounted(() => layoutStore.clearHeaderActions())
}
