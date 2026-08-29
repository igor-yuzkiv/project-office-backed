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
                {
                    path: ':documentId/tasks',
                    name: 'project-documentation.document.tasks',
                    component: () => import('@/pages/documentation/workspace/tabs/DocumentTasksPage.vue'),
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
                {
                    path: 'related-docs',
                    name: 'task-details.related-docs',
                    component: () => import('@/pages/tasks/details/tabs/TaskRelatedDocsPage.vue'),
                },
                {
                    path: 'owners',
                    name: 'task-details.owners',
                    component: () => import('@/pages/tasks/details/tabs/TaskOwnersPage.vue'),
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
            path: '/task-lists',
            name: 'task-lists',
            component: () => import('@/pages/task-lists/list/TaskListsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Task Lists' },
        },
        {
            path: '/task-lists/:id/edit',
            name: 'task-list-edit',
            component: () => import('@/pages/task-lists/edit/TaskListEditPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Edit Task List' },
        },
        {
            path: '/task-lists/:id',
            name: 'task-list-details',
            component: () => import('@/pages/task-lists/details/TaskListDetailsPage.vue'),
            meta: { requiresAuth: true, layout: 'default', title: 'Task List' },
            redirect: (to) => ({ name: 'task-list-details.description', params: to.params }),
            children: [
                {
                    path: 'description',
                    name: 'task-list-details.description',
                    component: () => import('@/pages/task-lists/details/tabs/TaskListDescriptionPage.vue'),
                },
                {
                    // The tab was called Overview until it stopped holding one; links that
                    // predate the rename still land on it.
                    path: 'overview',
                    redirect: (to) => ({ name: 'task-list-details.description', params: to.params }),
                },
                {
                    path: 'tasks',
                    name: 'task-list-details.tasks',
                    component: () => import('@/pages/task-lists/details/tabs/TaskListTasksPage.vue'),
                },
                {
                    path: 'comments',
                    name: 'task-list-details.comments',
                    component: () => import('@/pages/task-lists/details/tabs/TaskListCommentsPage.vue'),
                },
                {
                    path: 'attachments',
                    name: 'task-list-details.attachments',
                    component: () => import('@/pages/task-lists/details/tabs/TaskListAttachmentsPage.vue'),
                },
            ],
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
