---
type: task
status: todo
---

# 001 - Comments MVP

## Goal

Реалізувати v1 коментарів для задач: polymorphic backend domain з REST API і Policy-авторизацією + frontend widget на task details page з композером та списком. Без realtime, без notifications, без replies.

## Context

Користувач хоче коментарі на сторінці задачі. UI-референс: `.project_office/design/concept/task_detail_view.png`. Backlog нотатка: `.project_office/backlog/comments.md` (`search`, `reply`, `threads` — все відкладено).

Дата-модель polymorphic щоб у майбутньому розширити на Project та інші entity без міграції. v1 експонує endpoints і UI тільки під Task.

Реалізація використовує існуючі патерни проєкту:
- Backend Domain pattern: `app/Domains/{Entity}/{Actions/{Verb}{Entity}/{Verb}{Entity}Handler.php, Models/, Queries/}`.
- HTTP pattern: `app/Http/{Controllers/{Entity}/, Requests/{Entity}/, Resources/{Entity}/}`.
- Frontend FSD: `entities/{entity}/{api,types,queries,mutations}` + `widgets/{feature}/{ui,composables,index.ts}`.

Reverb broadcasting вже сконфігурований (`config/broadcasting.php`, `laravel-echo`, `pusher-js`) — не чіпається. Notifications scaffold відсутній — не створюється.

## Scope

### 1. Backend: Migration

Файл: `database/migrations/{timestamp}_create_comments_table.php`.

Таблиця `comments`:
- `id` — bigint PK
- `commentable_id` — unsignedBigInteger, indexed
- `commentable_type` — string, indexed
- `author_id` — foreignId → `users.id`, onDelete cascade (узгодити з конвенцією проєкту: у проєкті подивитись чи інші моделі юзають cascade чи restrict для `created_by`-style FK; узгодити з тим самим стилем)
- `content` — text (NOT nullable)
- composite index `[commentable_type, commentable_id]`
- `timestamps()`
- БЕЗ `deleted_at` (hard delete)

### 2. Backend: Domain

Папка: `app/Domains/Comment/`.

Структура:
- `Models/CommentModel.php` — extends Eloquent, fillable: `commentable_id`, `commentable_type`, `author_id`, `content`. Relations: `author()` (belongsTo User), `commentable()` (morphTo). PHPDoc `@property` для PHPStan лев5.
- `Actions/CreateComment/CreateCommentHandler.php` + `CreateCommentCommand.php` — приймає commentable (Task), author, content; створює CommentModel.
- `Actions/UpdateComment/UpdateCommentHandler.php` + `UpdateCommentCommand.php` — оновлює content.
- `Actions/DeleteComment/DeleteCommentHandler.php` — `delete()` на моделі.
- (Опційно) `Queries/TaskCommentsQuery.php` — інкапсулює paginated fetch з eager-load `author`.

Polymorphic morph map: явно зареєструвати alias у `app/Providers/AppServiceProvider.php` (`Relation::enforceMorphMap(['task' => TaskModel::class])`) щоб `commentable_type` зберігав alias `'task'`, а не FQCN. Якщо вже є morph map — додати запис.

`TaskModel` — додати relation:

```php
public function comments(): MorphMany
{
    return $this->morphMany(CommentModel::class, 'commentable');
}
```

### 3. Backend: HTTP layer

- `app/Http/Controllers/Comment/CommentController.php`:
  - `index(Task $task)` — paginated comments для задачі (eager-load author, sort `created_at asc`)
  - `store(StoreCommentRequest, Task $task)` — Authorize via Policy, виклик CreateCommentHandler
  - `update(UpdateCommentRequest, Comment $comment)` — Authorize via Policy, виклик UpdateCommentHandler
  - `destroy(Comment $comment)` — Authorize via Policy, виклик DeleteCommentHandler
- `app/Http/Requests/Comment/StoreCommentRequest.php` — `content`: required, string, max:5000.
- `app/Http/Requests/Comment/UpdateCommentRequest.php` — те саме.
- `app/Http/Resources/Comment/CommentResource.php` — поля:
  - `id`
  - `content` (raw markdown)
  - `author`: `{ id, name, ...(avatar surface як в інших Resource) }`
  - `created_at`
  - `updated_at`
  - `can`: `{ update: bool, delete: bool }` — обчислюється через `$request->user()->can('update', $comment)` / `can('delete', $comment)`.

### 4. Backend: Policy + routes

- `app/Policies/CommentPolicy.php`:
  - `viewAny(User)` → `true` (будь-який автентифікований)
  - `view(User, Comment)` → `true`
  - `create(User)` → `true`
  - `update(User, Comment)` → `$comment->author_id === $user->id`
  - `delete(User, Comment)` → `$comment->author_id === $user->id`
- Реєстрація policy: автодіскавер Laravel за конвенцією або в `AuthServiceProvider::$policies`.
- `routes/api.php` (всередині `auth:sanctum` групи):
  - `Route::get('tasks/{task}/comments', [CommentController::class, 'index']);`
  - `Route::post('tasks/{task}/comments', [CommentController::class, 'store']);`
  - `Route::patch('comments/{comment}', [CommentController::class, 'update']);`
  - `Route::delete('comments/{comment}', [CommentController::class, 'destroy']);`

### 5. Frontend: `entities/comment/`

Папка: `resources/js/entities/comment/`.

- `types/index.ts` — `Comment` тип за відповіддю `CommentResource`.
- `api/index.ts` — `fetchTaskComments(taskId, page)`, `createComment(taskId, payload)`, `updateComment(commentId, payload)`, `deleteComment(commentId)`.
- `queries/useTaskCommentsQuery.ts` — Vue Query infinite query або paginated query за конвенцією, що вже використовується в інших entity (перевірити як у `task` / `project`).
- `mutations/useCreate/Update/DeleteCommentMutation.ts` — з invalidation відповідного query.
- `config/index.ts` — query keys, route names якщо є.
- `index.ts` — public API.

### 6. Frontend: `widgets/comments/`

Папка: `resources/js/widgets/comments/`.

- `ui/CommentsTab.vue` — корневий компонент таба:
  - props: `taskId`
  - використовує `useTaskCommentsQuery(taskId)`
  - рендерить список через `CommentItem` + composer внизу через `CommentComposer`
  - порядок: chronological asc (старіші зверху)
  - паджінація: load-more кнопка або pagination, узгодити з тим що в інших паджінованих списках проєкту
- `ui/CommentItem.vue`:
  - props: `comment: Comment`
  - рендерить: `UserAvatar`, ім'я автора, `created_at` через `DisplayDate`-style або `date-fns`, body через `MarkdownPreview`
  - якщо `comment.can.update` → інлайн edit (toggle composer в edit-mode)
  - якщо `comment.can.delete` → confirm-dialog + виклик `deleteComment` mutation
- `ui/CommentComposer.vue`:
  - props: `taskId`, `initialContent?`, `mode: 'create' | 'edit'`, `commentId?`
  - використовує `md-editor` (як в `description`-формах)
  - сабміт → відповідна mutation; optimistic update або Vue Query refetch — узгодити з конвенцією проєкту
- `index.ts` — експорт `CommentsTab`.

### 7. Інтеграція таба в Task details page

Файл: `resources/js/pages/tasks/details/` — додати таб "Comments" у наявний tab-layout (поряд із Overview, Timeline тощо). Перевірити як інші таби реєструються (можливо є tabs config).

## Out Of Scope

- Threads / replies.
- Realtime / Reverb / Echo subscription.
- Laravel Notification scaffold чи dispatch events.
- Mentions `@user`.
- Реакції / likes / emojis.
- Attachments всередині коментаря.
- Search.
- Soft delete / restore / audit log.
- Role-badge ("Project Owner" в мокапі).
- Comments на Project або іншому entity.
- Admin override на edit/delete чужих коментарів.
- Edit window / time-limited editing.
- Зміни в Reverb config або у broadcast wiring.

## Expected Behavior

- На сторінці задачі є таб "Comments". Клік відкриває список коментарів задачі.
- Список рендериться знизу композером — хронологічно asc (як у мокапі).
- Авторизований user може написати markdown-коментар → після сабміту коментар з'являється в списку без перезавантаження сторінки.
- Автор бачить кнопки Edit / Delete тільки на своїх коментарях. Інші користувачі — не бачать.
- Edit перемикає інлайн-composer на режим редагування існуючого коментаря.
- Delete просить підтвердження, потім видаляє з БД (hard delete) і прибирає з UI.
- Список пагінується (стандартний Laravel paginator). Якщо коментарів менше за per_page — пагінація не видна.
- Контент рендериться як markdown (`MarkdownPreview`).
- Невалідний content (порожній або > 5000 символів) — 422 з помилкою валідації, UI показує помилку.

## Technical Notes

- **Polymorphic morph map**: явно зареєструвати alias (`Relation::enforceMorphMap`) щоб `commentable_type` не зберігав FQCN. Якщо в проєкті ще немає morph map — додати в `AppServiceProvider::boot()`.
- **Content max length**: 5000 символів — стартова стеля; якщо реалізатор вважає за потрібне інше — узгодити з автором.
- **API resource `can.*`**: повертати boolean прямо з resource (не окремий endpoint), щоб FE мав state одразу.
- **Sort**: `created_at asc` фіксований в API; FE НЕ передає sort param у v1.
- **Pagination per_page**: за конвенцією, що вже використовується (`task`, `project` paginated lists) — узгодити.
- **Cascade на user_id**: подивитись чи інші моделі юзають cascade чи restrict для `created_by`/`author_id`. Використати той самий стиль.
- **Eager loading**: `index` завжди eager-load `author` (одна query замість N+1).
- **PHPStan**: `@property` PHPDoc на `CommentModel` для всіх полів і relations.
- **Архітектурний hook під Notifications**: НЕ додавати event-дзвінки у Handlers. Коли Notifications-домен буде впроваджено — простий додаток у тіло Handler (`event(new CommentCreated($comment))`). Не пишемо event-класи в v1.
- **Архітектурний hook під Realtime**: жодних `ShouldBroadcast` чи каналів зараз. Майбутнє підключення — додати `implements ShouldBroadcast` на existing event.
- **Розбиття роботи по саб-агентах**:
  - sub-agent A → міграція + `CommentModel` + morph map + relation на `TaskModel`
  - sub-agent B → Handlers (Create/Update/Delete) + Policy (залежить від A)
  - sub-agent C → Controller + FormRequests + Resource + routes (залежить від B)
  - sub-agent D → `entities/comment/` (api/types/queries/mutations) (залежить від C, для типів)
  - sub-agent E → `widgets/comments/` + інтеграція таба (залежить від D)
  - A → B → C → D → E послідовно; всередині C можна паралелити (Controller / FormRequests / Resource / routes).

## Acceptance Criteria

- [ ] Міграція створює таблицю `comments` з полями і індексами як описано (без `deleted_at`).
- [ ] `app/Domains/Comment/` створено за конвенцією; `CommentModel` має fillable, relations і PHPDoc.
- [ ] Polymorphic morph map зареєстровано (`'task'` → `TaskModel`), `commentable_type` зберігає alias.
- [ ] `TaskModel` має `comments(): MorphMany` relation.
- [ ] Handlers `CreateComment`, `UpdateComment`, `DeleteComment` реалізовано через Command DTO.
- [ ] Policy `CommentPolicy` забороняє update/delete для не-автора.
- [ ] REST endpoints працюють: `GET /api/tasks/{task}/comments`, `POST /api/tasks/{task}/comments`, `PATCH /api/comments/{comment}`, `DELETE /api/comments/{comment}`.
- [ ] FormRequest валідація: `content` required, string, max:5000.
- [ ] `CommentResource` повертає `id`, `content`, `author{id,name,avatar surface}`, `created_at`, `updated_at`, `can.update`, `can.delete`.
- [ ] Frontend `entities/comment/` створено за FSD конвенцією з queries/mutations/types.
- [ ] Frontend `widgets/comments/CommentsTab.vue` рендерить список + композер.
- [ ] Інлайн edit/delete доступні тільки коли `can.update`/`can.delete` true.
- [ ] Таб "Comments" інтегрований у `pages/tasks/details/`.
- [ ] `./vendor/bin/pint` — clean.
- [ ] `./vendor/bin/phpstan analyse` — clean.
- [ ] `npm run format`, `npm run lint`, `npm run types:check` — clean.

## Open Questions

(всі критичні питання вирішені; мінорні параметри — у Technical Notes)

## Notes For Developer Agent

- НЕ додавати dispatch Laravel events у Handlers. NoEvents = свідоме архітектурне рішення v1.
- НЕ підключати Echo / Reverb broadcast у v1. Existing wiring не міняти.
- НЕ створювати Notification-класи.
- Polymorphic morph map ОБОВ'ЯЗКОВО з alias `'task'` — не FQCN. Це блокер для майбутнього перейменування моделей.
- Hard delete — підтверджений вибір. Не додавати `SoftDeletes` trait.
- Перед стартом FE-частини: переглянути як `task` / `project` paginated queries написані у Vue Query, повторити патерн (cursor / infinite / paginated — узгодити з тим, що вже є).
- Перед мерджем: візуально звірити з мокапом `.project_office/design/concept/task_detail_view.png` (нижня секція з табами "Comments / Activity / Attachments / Recommendations").
