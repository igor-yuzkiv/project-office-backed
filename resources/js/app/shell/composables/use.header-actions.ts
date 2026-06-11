import { onMounted, onUnmounted } from 'vue'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import type { HeaderAction } from '@/app/shell/types'

export function useHeaderActions(actions: HeaderAction[]) {
    const layoutStore = useAppLayoutStore()

    onMounted(() => layoutStore.setHeaderActions(actions))
    onUnmounted(() => layoutStore.clearHeaderActions())
}
