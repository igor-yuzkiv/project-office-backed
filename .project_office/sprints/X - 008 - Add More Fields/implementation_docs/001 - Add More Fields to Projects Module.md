# 001 - Add More Fields to Projects Module

## Що реалізовано

Розширено модуль `Project` новими полями, інфраструктурою архівування та окремою edit-сторінкою.

## Змінені файли / модулі

### Backend

**Міграція**
- `database/migrations/2026_05_30_113928_create_projects_table.php` — додано `description` (longText, nullable), `start_date` (date, nullable), `end_date` (date, nullable), `archived_at` (timestamp, nullable), `archived_by` (foreignUlid → users, nullable, nullOnDelete).

**Enum**
- `app/Domains/Project/Enums/ProjectStatus.php` — зведено до 5 case-ів: `DRAFT`, `ACTIVE`, `ON_HOLD`, `COMPLETED`, `ARCHIVED`. Видалено `INACTIVE` та `CANCELLED`.

**Infrastructure**
- `app/Infrastructure/Models/Contracts/Archivable.php` — новий інтерфейс з одним методом `wasStatusChangedToArchived(): bool`.
- `app/Infrastructure/Models/Concerns/HasArchivableColumns.php` — новий трейт. Boot listener на `saving` event. Archive: якщо `wasStatusChangedToArchived()` → встановлює `archived_at = now()`, `archived_by = auth()->id()`. Unarchive: якщо `isDirty('status') && getRawOriginal('status') === 'archived'` → очищає обидва поля. Важливо: використовує `getRawOriginal('status')` (не `getOriginal`), бо в Laravel 12 `getOriginal()` повертає cast-значення (enum), а не raw рядок.

**Domain**
- `app/Domains/Project/Models/ProjectModel.php` — реалізує `Archivable`, підключає `HasArchivableColumns`, новий `archivedBy()` BelongsTo, оновлені `#[Fillable]`, `casts()`, PHPDoc `@property`.
- `app/Domains/Project/Actions/CreateProject/CreateProjectCommand.php` — додано `?Carbon $startDate`, `?Carbon $endDate`.
- `app/Domains/Project/Actions/CreateProject/CreateProjectHandler.php` — пробрасовано нові поля у `create()`.
- `app/Domains/Project/Actions/UpdateProject/UpdateProjectCommand.php` — додано `?Carbon $startDate`, `?Carbon $endDate`; видалено `prefix`.
- `app/Domains/Project/Actions/UpdateProject/UpdateProjectHandler.php` — видалено `prefix` з `array_filter`; `start_date`/`end_date` завжди включені (null = clear).

**HTTP**
- `app/Http/Requests/Projects/StoreProjectRequest.php` — нові правила для `description`, `start_date`, `end_date`; метод `toCommand(): CreateProjectCommand` з Carbon-парсингом дат.
- `app/Http/Requests/Projects/UpdateProjectRequest.php` — нові правила, видалено `prefix`; метод `toCommand(ProjectModel $project): UpdateProjectCommand`.
- `app/Http/Controllers/Projects/ProjectsController.php` — `store()`/`update()` делегують `$request->toCommand()`; `show()` включає `archivedBy` у required includes; дозволений include `archivedBy`.
- `app/Http/Resources/Projects/ProjectResource.php` — додано `description`, `start_date`, `end_date`, `archived_at`, `archived_by` (whenLoaded).

**Tests**
- `tests/Feature/Http/Projects/ProjectArchiveTest.php` — 3 feature-тести: archive встановлює поля, unarchive очищає поля, зміна не-archive статусу не зачіпає archive-поля.

### Frontend

**Entity layer**
- `resources/js/entities/project/types/project-status.types.ts` — `ProjectStatusValue` зведено до 5 значень.
- `resources/js/entities/project/config/project-status.config.ts` — `ProjectStatusMap` 5 записів.
- `resources/js/entities/project/types/project.types.ts` — `IProject` отримав `description`, `start_date`, `end_date`, `archived_at`, `archived_by?`.
- `resources/js/entities/project/types/project.api.types.ts` — `ICreateProjectInput`/`IUpdateProjectInput` отримали нові поля; `prefix` видалено з `IUpdateProjectInput`; `ProjectInclude` додано `archivedBy`.
- `resources/js/entities/project/config/project-attachment.config.ts` — новий файл: `PROJECT_ATTACHMENT_ROLES.DESCRIPTION = 'projects.description'`.
- `resources/js/entities/project/config/index.ts` — додано експорт `project-attachment.config`.
- `resources/js/entities/project/api/project.api.ts` — `fetchProjectRequest` без явного include (archivedBy тепер у required includes на бекенді).

**Widgets**
- `resources/js/widgets/projects/create-dialog/` — новий widget (перейменований з `upsert-dialog`). Тільки поле `Name`, тільки create-сценарій.
- `resources/js/widgets/projects/upsert-dialog/` — видалено.

**Pages**
- `resources/js/pages/projects/list/ProjectsPage.vue` — переключено на `ProjectCreateDialog`/`useProjectCreateDialog`; row menu Edit → `router.push({ name: 'project-edit', ... })`.
- `resources/js/pages/projects/edit/ProjectEditPage.vue` — нова сторінка. Поля: Name, Status (Select 5 опцій), Start Date, End Date (DatePicker), Tags, Description (MarkdownEditor). Header actions: Save / Cancel. Breadcrumbs. Форма ініціалізується через `watch(project, ..., { immediate: true })` з guard `isFormInitialized`.
- `resources/js/pages/projects/details/ProjectDetailsPage.vue` — header action `Edit` → redirect на `project-edit`.
- `resources/js/pages/projects/details/tabs/ProjectOverviewPage.vue` — нові поля у grid (Start Date, End Date, Archived At/By conditional); MarkdownPreview description внизу.
- `resources/js/app/router/index.ts` — маршрут `/projects/:id/edit` (`project-edit`).

## Важливі рішення та компроміси

- **`getRawOriginal` для unarchive**: В Laravel 12 `getOriginal('status')` повертає enum (через cast), тому порівняння зі строкою `'archived'` завжди false. Використано `getRawOriginal('status')` для отримання сирого значення з БД.
- **`toCommand()` у FormRequest**: Логіка побудови Command (Carbon-парсинг дат, enum-конвертація статусу) переміщена у FormRequest, контролер залишається тонким.
- **Дати як `?Carbon` у Command**: Замість рядка, щоб домен працював з типізованими об'єктами. Carbon сумісний з Eloquent date cast.
- **`archivedBy` у required includes**: `ProjectsController::show()` завжди завантажує `archivedBy`, фронт не передає explicit include.
- **`prefix` видалено з update**: Prefix не редагується після створення — видалено з `UpdateProjectCommand`, `UpdateProjectRequest` і `IUpdateProjectInput`.
- **БД перевикочена**: Всі зміни схеми в існуючій міграції, `migrate:fresh --seed` без помилок.

## Перевірки

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` (level 5) — passed, 0 errors
- `php artisan test --filter="ProjectSearch|ProjectArchive"` — 11/11 passed
- `npm run format` — passed
- `npm run types:check` — passed
