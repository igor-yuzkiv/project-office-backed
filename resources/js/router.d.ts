// router.d.ts
import 'vue-router'
import type { AppLayoutName } from '@/app/layouts'

declare module 'vue-router' {
    interface RouteMeta {
        requiresAuth?: boolean
        guest?: boolean
        layout?: AppLayoutName
    }
}
