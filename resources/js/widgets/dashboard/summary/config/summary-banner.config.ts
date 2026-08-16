import type { RouteLocationRaw } from 'vue-router'

type SummaryBannerAccent = 'violet' | 'blue' | 'cyan' | 'emerald' | 'amber' | 'none'

/** Icon and description are UI copy; the label always comes from the endpoint. */
export type SummaryBannerDef = {
    icon: string
    accent: SummaryBannerAccent
    description?: string
}

export type SummaryBanner = SummaryBannerDef & {
    key: string
    label: string
    count: number
    to: RouteLocationRaw
}

export const SUMMARY_BANNER_REGISTRY: Record<string, SummaryBannerDef> = {
    all: {
        icon: 'heroicons:clipboard-document-check',
        accent: 'violet',
        description: 'Total tasks across all projects',
    },
    all_open: { icon: 'heroicons:list-bullet', accent: 'blue', description: 'Tasks not yet completed' },
    all_in_progress: { icon: 'heroicons:arrow-path', accent: 'cyan', description: 'Tasks currently in progress' },
    all_closed: { icon: 'heroicons:check-circle', accent: 'emerald', description: 'Tasks completed' },
    all_backlogged: { icon: 'heroicons:square-3-stack-3d', accent: 'amber', description: 'Tasks not yet started' },

    projects: { icon: 'heroicons:folder', accent: 'violet', description: 'Active projects' },
    task_lists: { icon: 'heroicons:queue-list', accent: 'cyan', description: 'Across all projects' },
}

const UNKNOWN_SUMMARY_BANNER: SummaryBannerDef = {
    icon: 'heroicons:question-mark-circle',
    accent: 'none',
}

/** A view added to the backend registry must render, not break the row. */
export function resolveSummaryBanner(key: string): SummaryBannerDef {
    return SUMMARY_BANNER_REGISTRY[key] ?? UNKNOWN_SUMMARY_BANNER
}

export const SUMMARY_BANNER_ACCENT_CLASSES: Record<SummaryBannerAccent, string> = {
    violet: 'bg-violet-100 text-violet-600 dark:bg-violet-950 dark:text-violet-400',
    blue: 'bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
    cyan: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-950 dark:text-cyan-400',
    emerald: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400',
    amber: 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400',
    none: 'bg-surface-200 text-surface-500 dark:bg-surface-700 dark:text-surface-300',
}
