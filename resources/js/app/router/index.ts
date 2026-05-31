import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import type { RouteLocationNormalized } from 'vue-router'

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/pages/LoginPage.vue'),
            meta: { guest: true, layout: 'auth' },
        },
        {
            path: '/',
            name: 'home',
            component: () => import('@/pages/HomePage.vue'),
            meta: { requiresAuth: true, layout: 'default' },
        },
    ],
})

router.beforeEach(async (to: RouteLocationNormalized) => {
    const authStore = useAuthStore()

    if (!authStore.initialized) {
        await authStore.initialize()
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return { name: 'login' }
    }

    if (to.meta.guest && authStore.isAuthenticated) {
        return { name: 'home' }
    }
})

export default router
