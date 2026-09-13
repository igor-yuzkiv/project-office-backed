import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import type { ThemedStatusColors } from '@/shared/types'
import { pickStatusColors } from './status-pill.util'

export function useStatusColors(colors: MaybeRefOrGetter<ThemedStatusColors>) {
    const theme = useAppThemeStore()

    return computed(() => pickStatusColors(toValue(colors), theme.isDark))
}
