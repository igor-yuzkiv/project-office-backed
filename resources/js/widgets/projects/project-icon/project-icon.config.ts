import type { ComponentSize } from '@/shared/types'

export const PROJECT_ICON_SIZE_MAP: Record<ComponentSize, { root: string; label: string }> = {
    xsmall: { root: '!h-5 !w-5', label: '!text-[9px]' },
    small: { root: '!h-6 !w-6', label: '!text-[10px]' },
    medium: { root: '!h-8 !w-8', label: '!text-xs' },
    large: { root: '!h-10 !w-10', label: '!text-sm' },
    xlarge: { root: '!h-12 !w-12', label: '!text-base' },
}
