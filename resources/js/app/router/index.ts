import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/app/stores/use.auth.store'
import type { RouteLocationNormalized } from 'vue-router'

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/pages/login/LoginPage.vue'),
            meta: { guest: true, layout: 'auth' },
        },
        {
            path: '/',
            name: 'home',
            component: () => import('@/pages/home/HomePage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Home' },
        },
        {
            path: '/projects',
            name: 'projects',
            component: () => import('@/pages/projects/ProjectsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Projects' },
        },
        {
            path: '/projects/:id',
            name: 'project-details',
            component: () => import('@/pages/projects/ProjectDetailsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Project' },
        },
        {
            path: '/tasks',
            name: 'tasks',
            component: () => import('@/pages/tasks/TasksPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Tasks' },
        },
        {
            path: '/documents',
            name: 'documents',
            component: () => import('@/pages/documents/DocumentsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Documents' },
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
