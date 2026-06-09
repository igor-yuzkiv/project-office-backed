# 006 - Create Task Dialog

## Що реалізовано

- Мінімальний create task dialog на `TasksPage` — відкривається через header action `Add Task`.
- Порожня `TaskDetailsPage` (placeholder) та `TaskEditPage` (placeholder).
- Маршрути `task-details` (`/tasks/:id`) та `task-edit` (`/tasks/:id/edit`).
- Row click на `TasksPage` → перехід на `task-details`.
- Header action `Edit Task` на `TaskDetailsPage` з route link на `task-edit`.
- Backend contract: `priority` у create task більше не required; nullable на рівні DB, validation, command, resource.
- Адаптація `HeaderAction` під route links (`to?: RouteLocationRaw`) + оновлений `HeaderActionButton`.
- Sidebar `activeWhen` — замінено `activeFor: string[]` на `string | callback` з path-prefix логікою.

## Змінені файли

### Backend
| Файл | Зміна |
|------|-------|
| `database/migrations/2026_05_31_130000_create_tasks_table.php` | `priority` → `nullable()` |
| `app/Http/Requests/Tasks/StoreTaskRequest.php` | `priority` → `nullable` |
| `app/Domains/Task/Actions/CreateTask/CreateTaskCommand.php` | `?TaskPriority $priority = null` |
| `app/Domains/Task/Actions/CreateTask/CreateTaskHandler.php` | `$command->priority?->value` |
| `app/Domains/Task/Models/TaskModel.php` | PHPDoc `TaskPriority\|null` |
| `app/Domains/Task/ValueObjects/TaskPriorityData.php` | Новий VO: `Arrayable`, `from(TaskPriority)` |
| `app/Http/Resources/Tasks/TaskResource.php` | `TaskPriorityData::from()->toArray()` або `null` |
| `app/Http/Controllers/Tasks/TasksController.php` | Null-safe priority у `store()` |
| `tests/Feature/Http/Tasks/TaskStoreTest.php` | Новий: без priority, з priority, invalid priority |

### Frontend
| Файл | Зміна |
|------|-------|
| `entities/task/types/task.types.ts` | `ITask.priority: ITaskPriority\|null`, `ICreateTaskInput.priority` optional |
| `entities/task/mutations/use.create-task.mutation.ts` | Новий |
| `entities/task/mutations/index.ts` | Новий |
| `widgets/tasks/create-dialog/composables/use.task-create-dialog.ts` | Новий: formData ref, getDefaultFormData, open(initialProject?) |
| `widgets/tasks/create-dialog/ui/TaskCreateDialog.vue` | Новий: Dialog + InputText + AutoComplete з loading |
| `widgets/tasks/create-dialog/index.ts` | Новий |
| `pages/tasks/TaskDetailsPage.vue` | Placeholder + header action Edit Task (route link) |
| `pages/tasks/TaskEditPage.vue` | Placeholder |
| `pages/tasks/TasksPage.vue` | Підключений dialog, row click → task-details, nullable priority column |
| `app/router/index.ts` | Routes: `task-details`, `task-edit` |
| `app/shell/types/index.ts` | `HeaderAction.to?`, `SidebarNavItem.activeWhen` замість `activeFor` |
| `app/shell/ui/header/HeaderActionButton.vue` | RouterLink як `as` prop коли є `to` |
| `app/shell/ui/navigation/AppLeftNavigationSidebar.vue` | `isActive` via path prefix або callback |
| `app/shell/ui/layouts/DefaultLayout.vue` | `activeWhen` для всіх nav items |

## Важливі рішення

- **`TaskPriorityData` VO** — серіалізація priority винесена з `TaskResource` у окремий value object з `Arrayable`.
- **`formData` як `ref`** — єдиний об'єкт стану форми; `getDefaultFormData()` винесена за межі composable.
- **Пошук проектів у компоненті** — `projectSearchTerm`, debounce, query живуть у `TaskCreateDialog.vue`, не в composable.
- **`handleFieldChanged(key, value)`** — єдиний метод оновлення полів через `emit('update:formData', {...})`.
- **`ProjectOverviewDto`** замість повного `IProject` у `TaskCreateFormData.project`.
- **`activeWhen`** — замінив `activeFor: string[]`; підтримує string (startsWith) або `(item, route) => boolean`.
- **`HeaderAction.to`** — опціональний `RouteLocationRaw`; `action` стало опціональним; `HeaderActionButton` рендерить `RouterLink` через `as` prop.

## Перевірки

- Backend: Pint ✓, PHPStan ✓, `TaskStoreTest` (3) ✓, `TaskSearchTest` (10) ✓
- Frontend: format ✓, lint ✓, types:check ✓
