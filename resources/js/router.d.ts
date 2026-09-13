// router.d.ts
import 'vue-router'
import type { AppLayoutName } from '@/app/shell'

declare module 'vue-router' {
    interface RouteMeta {
        requiresAuth?: boolean
        guest?: boolean
        layout?: AppLayoutName
        title?: string
        /** Mount a fresh page when this record's own params change (the page reads its id once). */
        remountOnParams?: boolean
    }
}
