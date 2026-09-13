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
            redirect: (to) => ({ name: 'project-details.overview', params: to.params }),
            children: [
                {
                    path: 'overview',
                    name: 'project-details.overview',
                    component: () => import('@/pages/projects/details/tabs/ProjectOverviewPage.vue'),
                },
                // Old URLs bookmarks may still carry.
                {
                    path: 'details',
                    redirect: (to) => ({ name: 'project-details.overview', params: to.params }),
                },
                {
                    path: 'attachments',
                    redirect: (to) => ({ name: 'project-details.overview', params: to.params }),
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
            ],
        },
        {
            path: '/projects/:projectId/documentation',
            component: () => import('@/pages/documentation/workspace/DocumentationWorkspacePage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Documentation' },
            children: [
                {
                    path: '',
                    name: 'project-documentation',
                    component: () => import('@/pages/documentation/workspace/tabs/DocumentationLandingPage.vue'),
                },
                {
                    path: ':documentId',
                    name: 'project-documentation.document',
                    component: () => import('@/pages/documentation/workspace/tabs/DocumentContentPage.vue'),
                },
                {
                    path: ':documentId/details',
                    name: 'project-documentation.document.details',
                    component: () => import('@/pages/documentation/workspace/tabs/DocumentDetailsPage.vue'),
                },
                {
                    path: ':documentId/comments',
                    name: 'project-documentation.document.comments',
                    component: () => import('@/pages/documentation/workspace/tabs/DocumentCommentsPage.vue'),
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
        },
        {
            path: '/projects/:id/edit',
            name: 'project-edit',
            component: () => import('@/pages/projects/edit/ProjectEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit project' },
        },
        {
            path: '/tasks/:id/edit',
            name: 'task-edit',
            component: () => import('@/pages/tasks/edit/TaskEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit task' },
        },
        {
            path: '/task-lists',
            name: 'task-lists',
            component: () => import('@/pages/task-lists/list/TaskListsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Task lists' },
        },
        {
            path: '/activity',
            name: 'activity',
            component: () => import('@/pages/activity/activity/ActivityPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Activity' },
        },
        {
            path: '/task-lists/:id/edit',
            name: 'task-list-edit',
            component: () => import('@/pages/task-lists/edit/TaskListEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit task list' },
        },
        {
            path: '/task-lists/:id',
            name: 'task-list-details',
            component: () => import('@/pages/task-lists/details/TaskListDetailsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Task list' },
        },
        {
            // Not a page: it resolves a document to its project and hands it to the
            // workspace. The activity stream and the documentation tables know only
            // the document.
            path: '/project-documents/:id',
            name: 'project-document-resolver',
            component: () => import('@/pages/documentation/resolver/ProjectDocumentResolverPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Document' },
        },
        {
            path: '/profile',
            name: 'profile',
            component: () => import('@/pages/user/CurrentUserProfilePage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Profile' },
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'not-found',
            component: () => import('@/pages/errors/not-found/NotFoundPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Not found' },
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
