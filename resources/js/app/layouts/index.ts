import AuthLayout from './AuthLayout.vue'
import DefaultLayout from './DefaultLayout.vue'

export type AppLayoutName = 'default' | 'auth'

export const AppLayoutComponentMap: Record<AppLayoutName, unknown> = {
    default: DefaultLayout,
    auth: AuthLayout,
}
