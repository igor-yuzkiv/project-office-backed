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
            meta: {
                requiresAuth: true,
                layout: 'default',
                title: 'Project',
            },
            redirect: (to) => ({ name: 'project-details.details', params: to.params }),
            children: [
                {
                    path: 'details',
                    name: 'project-details.details',
                    component: () => import('@/pages/projects/details/tabs/ProjectOverviewPage.vue'),
                },
                {
                    path: 'task-lists',
                    name: 'project-details.task-lists',
                    component: () => import('@/pages/projects/details/tabs/ProjectTaskListsPage.vue'),
                },
                {
                    path: 'tasks',
                    name: 'project-details.tasks',
                    component: () => import('@/pages/projects/details/tabs/ProjectTasksPage.vue'),
                },
                {
                    path: 'issues',
                    name: 'project-details.issues',
                    component: () => import('@/pages/projects/details/tabs/ProjectIssuesPage.vue'),
                },
                {
                    path: 'attachments',
                    name: 'project-details.attachments',
                    component: () => import('@/pages/projects/details/tabs/ProjectAttachmentsPage.vue'),
                },
                {
                    path: 'documentation',
                    name: 'project-details.documentation',
                    component: () => import('@/pages/projects/details/tabs/ProjectDocumentationPage.vue'),
                },
            ],
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
            redirect: (to) => ({ name: 'task-details.description', params: to.params }),
            children: [
                {
                    path: 'details',
                    name: 'task-details.details',
                    component: () => import('@/pages/tasks/details/tabs/TaskOverviewPage.vue'),
                },
                {
                    path: 'description',
                    name: 'task-details.description',
                    component: () => import('@/pages/tasks/details/tabs/TaskDescriptionPage.vue'),
                },
                {
                    path: 'comments',
                    name: 'task-details.comments',
                    component: () => import('@/pages/tasks/details/tabs/TaskCommentsPage.vue'),
                },
                {
                    path: 'attachments',
                    name: 'task-details.attachments',
                    component: () => import('@/pages/tasks/details/tabs/TaskAttachmentsPage.vue'),
                },
            ],
        },
        {
            path: '/projects/:id/edit',
            name: 'project-edit',
            component: () => import('@/pages/projects/edit/ProjectEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit Project' },
        },
        {
            path: '/tasks/:id/edit',
            name: 'task-edit',
            component: () => import('@/pages/tasks/edit/TaskEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit Task' },
        },
        {
            path: '/project-documents/:id',
            name: 'project-document-details',
            component: () => import('@/pages/project-documents/details/ProjectDocumentDetailsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Document' },
            redirect: (to) => ({ name: 'project-document-details.details', params: to.params }),
            children: [
                {
                    path: 'details',
                    name: 'project-document-details.details',
                    component: () => import('@/pages/project-documents/details/tabs/ProjectDocumentOverviewPage.vue'),
                },
                {
                    path: 'content',
                    name: 'project-document-details.content',
                    component: () => import('@/pages/project-documents/details/tabs/ProjectDocumentContentPage.vue'),
                },
                {
                    path: 'related-tasks',
                    name: 'project-document-details.related-tasks',
                    component: () =>
                        import('@/pages/project-documents/details/tabs/ProjectDocumentRelatedTasksPage.vue'),
                },
                {
                    path: 'comments',
                    name: 'project-document-details.comments',
                    component: () => import('@/pages/project-documents/details/tabs/ProjectDocumentCommentsPage.vue'),
                },
            ],
        },
        {
            path: '/project-documents/:id/edit',
            name: 'project-document-edit',
            component: () => import('@/pages/project-documents/edit/ProjectDocumentEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit Document' },
        },
        {
            path: '/profile',
            name: 'profile',
            component: () => import('@/pages/user/CurrentUserProfilePage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Profile' },
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
