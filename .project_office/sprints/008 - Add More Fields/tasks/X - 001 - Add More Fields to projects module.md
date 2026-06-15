---
type: task
status: ready
---

# 001 - Add More Fields to Projects Module

## Goal

Розширити модуль `Project` додатковими полями (`description`, `start_date`, `end_date`, `archived_at`, `archived_by`), скоротити набір статусів, винести логіку архівування у переюзовний трейт + інтерфейс, перенести редагування у нову окрему сторінку, а існуючий dialog звести до мінімального set полів для створення.

## Context

Сучасний модуль `Project` має тільки `name`, `prefix`, `status`. Цього недостатньо для повноцінного управління проектами. Окремо потрібна інфраструктура для архівування — вона буде перевикористана у sprint 008 task 002 (Tasks модуль), тому реалізовуємо її як generic-трейт + інтерфейс одразу.

Поточний `ProjectUpsertDialog` обслуговує і create, і update. Це конфліктує з планом мати full-edit page. Dialog зводимо до мінімуму (як `TaskCreateDialog`), а редагування переносимо на нову сторінку `/projects/:id/edit` (за прикладом `TaskEditPage`).

База даних містить тільки fake-дані — можна не створювати нові міграції, а відредагувати існуючі та перевикотити БД.

## Scope

### Backend — Schema & Enum

- Відредагувати `database/migrations/2026_05_30_113928_create_projects_table.php`:
  - додати `description` (`longText`, nullable);
  - додати `start_date` (`date`, nullable);
  - додати `end_date` (`date`, nullable);
  - додати `archived_at` (`timestamp`, nullable);
  - додати `archived_by` (foreignUlid → `users`, nullable, `nullOnDelete`).
- `App\Domains\Project\Enums\ProjectStatus`: видалити `INACTIVE` та `CANCELLED`. Фінальний набір: `DRAFT` (default), `ACTIVE`, `ON_HOLD`, `COMPLETED`, `ARCHIVED`.
- Перевірити що ніде у коді не лишилось рефенсів на видалені статуси (controllers, factories, seeders, frontend config).

### Backend — Archivable Trait & Interface

- Створити інтерфейс `App\Infrastructure\Models\Contracts\Archivable` з одним методом `wasStatusChangedToArchived(): bool`.
- Створити трейт `App\Infrastructure\Models\Concerns\HasArchivableColumns`:
  - boot listener на `saving` event;
  - якщо `wasStatusChangedToArchived()` повертає true → встановити `archived_at = now()`, `archived_by = auth()->id()`;
  - якщо попередній статус був `archived`, а новий — ні (unarchive) → очистити обидва поля (`null`);
  - getter-методи `getArchivedAtColumn()`, `getArchivedByColumn()` для можливого override.
- `ProjectModel`:
  - реалізувати `Archivable`;
  - підключити `HasArchivableColumns`;
  - реалізувати `wasStatusChangedToArchived()` через `isDirty('status')` + перевірку нового значення;
  - оновити `#[Fillable]` (додати `description`, `start_date`, `end_date`);
  - оновити `casts()` — `start_date`, `end_date` → `date`, `archived_at` → `datetime`;
  - оновити PHPDoc `@property` для нових полів.

### Backend — HTTP Layer

- `StoreProjectRequest` / `UpdateProjectRequest`: додати правила для `description` (`nullable`, `string`), `start_date` (`nullable`, `date`), `end_date` (`nullable`, `date`, `after_or_equal:start_date`). `archived_at` / `archived_by` — клієнт не передає (керується трейтом).
- `CreateProjectCommand` / `UpdateProjectCommand`: додати поля `description`, `startDate`, `endDate`.
- `CreateProjectHandler` / `UpdateProjectHandler`: пробросити нові поля у `create` / `update`.
- `ProjectsController`: пробросити нові поля у Command (також у `store` сценарії — поля nullable).
- `ProjectResource`: додати `description`, `start_date`, `end_date`, `archived_at`, `archived_by` (через `UserOverviewResource`, з whenLoaded на `archivedBy` relation).
- `ProjectModel`: додати `archivedBy()` BelongsTo relation, додати у allowed includes контролера.

### Frontend — Entity Layer

- `entities/project/types/project.types.ts` (`IProject`): додати `description: string | null`, `start_date: string | null`, `end_date: string | null`, `archived_at: string | null`, `archived_by?: UserOverviewDto`.
- `entities/project/types/project.api.types.ts` (`ICreateProjectInput`, `IUpdateProjectInput`): додати `description`, `start_date`, `end_date`.
- `entities/project/types/project-status.types.ts`: видалити `inactive`, `cancelled`.
- `entities/project/config/project-status.config.ts`: оновити мапу (5 статусів).
- `entities/project/config/`: додати `project-module.config.ts` (`PROJECT_MODULE_NAME = 'projects'`) та `project-attachment.config.ts` (`PROJECT_ATTACHMENT_ROLES.DESCRIPTION = 'projects.description'`) за патерном tasks.

### Frontend — Create Dialog

- Перейменувати папку/файл/composable `widgets/projects/upsert-dialog` → `widgets/projects/create-dialog`:
  - `ProjectUpsertDialog.vue` → `ProjectCreateDialog.vue`;
  - `use.project-upsert-dialog.ts` → `use.project-create-dialog.ts`;
  - тип `ProjectUpsertFormData` → `ProjectCreateFormData`.
- Видалити `mode: 'create' | 'update'` гілку — dialog обслуговує тільки create.
- Залишити у формі тільки **обов'язкові мінімальні поля** (за патерном `TaskCreateDialog`): `Name`. Статус не показуємо у create dialog — використовуємо backend default (`draft`). Tags теж прибираємо з create dialog (керуються на edit page).
- Оновити всі точки виклику (`pages/projects/list/ProjectsPage.vue`, можливо інші місця) — прибрати update-сценарій.

### Frontend — Project Edit Page

- Створити `pages/projects/edit/ProjectEditPage.vue` за прикладом `TaskEditPage`:
  - layout: верхній блок з полями у grid (як у TaskEditPage), внизу — `MarkdownEditor` для `description`.
  - editable поля: `Name`, `Status` (`Select` з 5 опціями), `Start Date` (`DatePicker`, nullable), `End Date` (`DatePicker`, nullable), `Tags`, `Description` (`MarkdownEditor` з `image_entity_type=PROJECT_MODULE_NAME`, `image_entity_id=projectId`, `image_role=PROJECT_ATTACHMENT_ROLES.DESCRIPTION`).
  - **`Prefix` — read-only** (показати як disabled `InputText` або як `DisplayField`).
  - Header actions: `Save` (primary), `Cancel`.
  - Breadcrumbs: `Projects` → `<project.name>` → `Edit`.
- Додати route `/projects/:id/edit` (`name: 'project-edit'`) у `app/router/index.ts`.

### Frontend — Project Details Page

- На `ProjectDetailsPage.vue` додати header action `Edit` (`useHeaderActions`), який робить `router.push({ name: 'project-edit', params: { id: projectId } })`.
- На `pages/projects/details/tabs/ProjectOverviewPage.vue` (tab `details`) додати read-only відображення нових полів **після існуючих** (у тому ж grid):
  - `Start Date`, `End Date` — через `DisplayDate`;
  - `Archived At` — через `DisplayDate`, показувати тільки якщо є значення;
  - `Archived By` — через UserAvatar блок (як `Created By`), показувати тільки якщо є значення;
  - Внизу, **під grid** — повноширинний блок `MarkdownPreview` для `description` (із плейсхолдером "No description available." якщо порожнє, аналогічно `TaskDescriptionPage`).

## Out Of Scope

- Окрема `ProjectAttachmentsPage` із UI для перегляду/керування attachments (існуючий `ProjectAttachmentsPage.vue` залишається заглушкою; attachments із роллю `projects.description` створюються через MarkdownEditor, але окремий перегляд — не в цьому sprint).
- Фільтрація проектів за датами або статусом `archived` на list-сторінці.
- Editing `prefix` після створення проекту.
- Зміна формату `prefix` чи його авто-генерації.
- Soft delete, історія змін, audit log архівування.
- Bulk archive/unarchive операції.
- Notifications/events при архівуванні.
- Tasks модуль — окремий task 002 цього sprint.

## Expected Behavior

- Створення проекту через dialog: тільки поле `Name` обов'язкове, статус автоматично `draft`. Інші поля заповнюються на edit page.
- На `ProjectDetailsPage` доступна кнопка `Edit` у header → веде на `/projects/:id/edit`.
- На edit page користувач може редагувати: name, status (5 опцій), start_date, end_date, tags, description (markdown + drag&drop картинок як attachments). Prefix відображається, але редагувати не можна.
- Збереження edit page → редіректить назад на details page; query інвалідуються, нові значення видно одразу.
- При зміні статусу на `archived`: backend автоматично записує `archived_at = now()`, `archived_by = current user`. Це видно на details сторінці.
- При зміні статусу з `archived` на будь-який інший: `archived_at` і `archived_by` очищаються.
- На details tab показані: name, prefix, status, дати, created_by/at, updated_by/at, archived_by/at (якщо є), tags, description (markdown preview або плейсхолдер).
- `end_date` не може бути раніше за `start_date` (validation error від backend).

## Technical Notes

- **БД можна повністю перевикотити** (`migrate:fresh --seed`) — фейкові дані. Тому редагуємо існуючу міграцію `2026_05_30_113928_create_projects_table.php`, нову не створюємо.
- Перед видаленням `INACTIVE`/`CANCELLED` зі enum — пройти `rg "ProjectStatus::INACTIVE|ProjectStatus::CANCELLED|'inactive'|'cancelled'"` по `app/`, `database/`, `resources/js/` та зачистити всі рефенси (factories, seeders, тести, frontend config, можливі фільтри).
- Трейт `HasArchivableColumns` має жити в `app/Infrastructure/Models/Concerns/` поряд з `HasAuditableColumns`. Інтерфейс `Archivable` — в новій теці `app/Infrastructure/Models/Contracts/`.
- Метод `wasStatusChangedToArchived()` реалізується через `$this->isDirty('status') && $this->status === ProjectStatus::ARCHIVED`. Це чистий read-only метод — він тільки відповідає на питання, без побічних ефектів.
- Unarchive-логіка повністю всередині трейта (`getOriginal('status') === 'archived' && new !== 'archived'`); інтерфейс її не вимагає.
- `MarkdownEditor` (`@/shared/components/md-editor`) і `MarkdownPreview` вже існують — використати їх 1:1 як у tasks. Не створювати нових компонентів.
- `DatePicker` — використати PrimeVue `DatePicker` (вже доступний у проекті — не встановлювати нових пакетів).
- `DisplayDate` — вже існує у `@/shared/components/display`, використати для start/end/archived_at у read-only режимі.
- Form initialization у `ProjectEditPage` — `watch(project, …, { immediate: true })` з guard `isFormInitialized`, як у `TaskEditPage`.
- Submit payload відправляє `null` для порожніх optional полів (description, start_date, end_date), як у `TaskEditPage` (`description || null`).
- Для PHPStan: додати `@property` PHPDoc для нових полів та `archivedBy` relation у `ProjectModel`.

## Acceptance Criteria

- [ ] Міграція `create_projects_table.php` містить нові колонки; `migrate:fresh --seed` проходить без помилок.
- [ ] `ProjectStatus` enum містить рівно 5 case-ів; жодних рефенсів на `inactive`/`cancelled` у репозиторії.
- [ ] Інтерфейс `Archivable` та трейт `HasArchivableColumns` створені у `app/Infrastructure/Models/`.
- [ ] `ProjectModel` реалізує `Archivable` і використовує `HasArchivableColumns`.
- [ ] При update проекту зі зміною статусу на `archived` — `archived_at` та `archived_by` заповнюються автоматично (підтвердити feature-тестом).
- [ ] При зміні статусу з `archived` на інший — `archived_at` та `archived_by` очищаються (підтвердити feature-тестом).
- [ ] `ProjectResource` повертає всі нові поля (`description`, `start_date`, `end_date`, `archived_at`, `archived_by`).
- [ ] `Store`/`UpdateProjectRequest` валідують `end_date >= start_date`.
- [ ] Create dialog має тільки поле `Name` і показує тільки при створенні; перейменований у `ProjectCreateDialog`.
- [ ] Маршрут `/projects/:id/edit` (`project-edit`) доступний; компонент `ProjectEditPage.vue` рендерить всі editable поля.
- [ ] Header action `Edit` на details page редіректить на edit page.
- [ ] Markdown editor у edit page підтримує drag&drop картинок з роллю `projects.description`.
- [ ] Tab `details` показує нові поля + markdown preview опису.
- [ ] `Prefix` на edit page відображений як read-only.
- [ ] Backend: `./vendor/bin/pint` + `./vendor/bin/phpstan analyse` проходять без помилок.
- [ ] Frontend: `npm run format` + `npm run lint` + `npm run types:check` проходять без помилок.
- [ ] Існуючі feature-тести Project API не зламані.

## Open Questions

- N/A

## Notes For Developer Agent

### Виконання через паралельні саб-агенти

Task великий, але добре розбивається на **частково паралельні зони**. Запропоноване розбиття для дев-агента:

**Етап 1 — Backend foundation (без паралелізації, блокує все інше):**
- Sub-agent A: міграція + `ProjectStatus` enum reduce + інтерфейс `Archivable` + трейт `HasArchivableColumns` + оновлення `ProjectModel` + Resource/Requests/Commands/Handlers + feature-тести на archive/unarchive.
- Виконати `pint` + `phpstan` + `php artisan test --filter=Project`.

**Етап 2 — Frontend, дві паралельні гілки (запускати одночасно після етапу 1):**

- Sub-agent B — **Entity layer + Create Dialog**:
  - Оновити `entities/project/` (types, config: status, module, attachment roles).
  - Перейменувати `upsert-dialog` → `create-dialog` (mode: тільки create, тільки поле Name).
  - Оновити всі точки виклику dialog у `pages/projects/list/ProjectsPage.vue` тощо.

- Sub-agent C — **Edit Page + Details Tab оновлення**:
  - Створити `pages/projects/edit/ProjectEditPage.vue` + route `/projects/:id/edit`.
  - Додати header action `Edit` на `ProjectDetailsPage`.
  - Оновити `ProjectOverviewPage` (нові поля + MarkdownPreview опису).
  - Залежить тільки від типів зі sub-agent B (можна стартувати після того, як B завершив `types/` оновлення; решту своєї роботи B може робити паралельно з C).

**Етап 3 — Validation:**
- `npm run format && npm run lint && npm run types:check`.
- Smoke test: створення проекту → edit → save → details. Тест архівування через status select.

### Інші важливі замітки

- Не міняти існуючу верстку deталей / списку більше, ніж потрібно для нових полів.
- Не вводити нових пакетів. `DatePicker`, `MarkdownEditor`, `DisplayDate`, `Select` — все вже є.
- При перейменуванні dialog зробити чистий rename (не лишати re-export з upsert-dialog).
- Інтерфейс `Archivable` свідомо мінімальний (один метод) — не розширювати "про запас".
- Task 002 цього sprint буде використовувати `HasArchivableColumns` + `Archivable` для `TaskModel` — переконатися, що трейт справді generic і не містить project-specific логіки.
