import type { ComponentSize } from '@/shared/types'

export const PROJECT_ICON_SIZE_MAP: Record<ComponentSize, { root: string; label: string }> = {
    xsmall: { root: '!h-6 !w-6', label: '!text-[9px]' },
    small: { root: '!h-7 !w-7', label: '!text-[10px]' },
    medium: { root: '!h-9 !w-9', label: '!text-xs' },
    large: { root: '!h-11 !w-11', label: '!text-sm' },
    xlarge: { root: '!h-13 !w-13', label: '!text-base' },
}
