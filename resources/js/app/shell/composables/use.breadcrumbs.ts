import { onScopeDispose, watchEffect } from 'vue'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import type { BreadcrumbItem } from '@/app/shell/types'

type BreadcrumbsGetter = BreadcrumbItem[] | (() => BreadcrumbItem[])

export function useBreadcrumbs(items: BreadcrumbsGetter) {
    const scopeId = Symbol('breadcrumbs-scope')
    const layoutStore = useAppLayoutStore()

    watchEffect(() => {
        const resolved = typeof items === 'function' ? items() : items
        layoutStore.setBreadcrumbs(scopeId, resolved)
    })

    onScopeDispose(() => layoutStore.clearBreadcrumbs(scopeId))
}
