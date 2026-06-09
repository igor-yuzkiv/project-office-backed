import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/app/stores/use.auth.store'
import type { RouteLocationNormalized } from 'vue-router'

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/pages/login/login/LoginPage.vue'),
            meta: { guest: true, layout: 'auth' },
        },
        {
            path: '/',
            name: 'home',
            component: () => import('@/pages/home/home/HomePage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Home' },
        },
        {
            path: '/projects',
            name: 'projects',
            component: () => import('@/pages/projects/list/ProjectsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Projects' },
        },
        {
            path: '/projects/:id',
            name: 'project-details',
            component: () => import('@/pages/projects/details/ProjectDetailsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Project' },
        },
        {
            path: '/tasks',
            name: 'tasks',
            component: () => import('@/pages/tasks/list/TasksPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Tasks' },
        },
        {
            path: '/tasks/:id',
            name: 'task-details',
            component: () => import('@/pages/tasks/details/TaskDetailsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Task' },
        },
        {
            path: '/tasks/:id/edit',
            name: 'task-edit',
            component: () => import('@/pages/tasks/edit/TaskEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit Task' },
        },
        {
            path: '/documents',
            name: 'documents',
            component: () => import('@/pages/documents/list/DocumentsPage.vue'),
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
