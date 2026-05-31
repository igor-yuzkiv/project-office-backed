# 010 - Add Frontend API Request Layer

## Що реалізовано

API request layer для всіх сутностей milestone. Рефакторинг `authApi` до нового стилю. `ApiError` клас з axios interceptor.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Оновлено | `resources/js/entities/user/api/auth.api.ts` |
| Створено | `resources/js/entities/project/api/project.api.ts` |
| Створено | `resources/js/entities/project/api/index.ts` |
| Створено | `resources/js/entities/task_list/api/task_list.api.ts` |
| Створено | `resources/js/entities/task_list/api/index.ts` |
| Створено | `resources/js/entities/task/api/task.api.ts` |
| Створено | `resources/js/entities/task/api/index.ts` |
| Створено | `resources/js/entities/attachment/api/attachment.api.ts` |
| Створено | `resources/js/entities/attachment/api/index.ts` |
| Створено | `resources/js/shared/api/api.error.ts` |
| Оновлено | `resources/js/shared/api/http.client.ts` |
| Оновлено | `resources/js/shared/api/index.ts` |
| Оновлено | `resources/js/stores/auth.store.ts` |

## Стиль API функцій

- Кожен endpoint — окрема іменована `async function` з суфіксом `Request`.
- GET операції — префікс `fetch` (`fetchProjectsRequest`, `fetchTaskRequest`).
- Мутації — `createXRequest`, `updateXRequest`, `deleteXRequest`, `uploadXRequest`.
- Кожна функція повертає розгорнуті дані через `.then((res) => res.data)` — без `response.data.data` у caller.
- Explicit return types: paginated → `PromisePaginatedResponse<T>`, single → `Promise<{ data: IEntity }>`.

## Routes

| Entity | Функції |
| --- | --- |
| User/Auth | `fetchCsrfCookieRequest`, `loginRequest`, `logoutRequest`, `fetchUserRequest` |
| Project | `fetchProjectsRequest`, `fetchProjectRequest`, `createProjectRequest`, `updateProjectRequest`, `deleteProjectRequest` |
| TaskList | `fetchTaskListsRequest`, `fetchTaskListRequest`, `createTaskListRequest`, `updateTaskListRequest`, `deleteTaskListRequest` |
| Task | `fetchTasksRequest`, `fetchTaskRequest`, `createTaskRequest`, `updateTaskRequest`, `deleteTaskRequest` |
| Attachment | `uploadAttachmentRequest` |

## ApiError

`shared/api/api.error.ts` — клас `ApiError extends Error`, типізований через `AxiosError<ApiErrorResponseData>`.

Геттери: `status`, `isValidationError`, `data`, `validationErrors`, `displayMessage`.

`http.client.ts` — response interceptor конвертує всі axios помилки в `ApiError` автоматично. Caller робить `catch (e)` → `if (e instanceof ApiError)`.

## Рішення

- `uploadAttachmentRequest` приймає типізований `IUploadAttachmentInput` і конвертує у `FormData` всередині функції.
- `authApi` об'єкт видалено — всі функції експортуються окремо.
- `auth.store.ts` оновлено під нові імена функцій.

## Перевірки

- `tsc --noEmit` — TypeScript чистий.

## Commit message

```
feat(frontend): add API request layer with ApiError and response interceptor
```
