---
type: task
status: draft
---

# 010 - Project Status Implementation

## Goal

Додати поле `status` до сутності Project та провести його наскрізно через backend і frontend. Поле обов'язкове на рівні БД, значення за замовчуванням — `DRAFT`. Значення обмежене enum'ом `ProjectStatus`.

## Context

Backend:

- Enum `App\Domains\Project\Enums\ProjectStatus` уже існує (`app/Domains/Project/Enums/ProjectStatus.php`) із 7 кейсами: `DRAFT`, `ACTIOVE`, `INACTIVE`, `ARCHIVED`, `COMPLETED`, `ON_HOLD`, `CANCELLED`. **Має друкарську помилку**: `ACTIOVE` → `ACTIVE`.
- Міграція `database/migrations/2026_05_30_113928_create_projects_table.php` — єдина міграція таблиці `projects`. Колонки `status` ще немає.
- `ProjectModel` (`app/Domains/Project/Models/ProjectModel.php`):
  - `#[Fillable(['id', 'name', 'prefix', 'created_by', 'updated_by'])]` — без `status`.
  - `casts(): []` — без enum-каста.
  - `toSearchableArray()` — повертає `id`, `name`, `prefix`.
  - `allowedFilters()` — `TextFilter` по `name`, `prefix`.
- Actions:
  - `CreateProjectCommand` / `CreateProjectHandler` — приймають лише `name`, `prefix`.
  - `UpdateProjectCommand` / `UpdateProjectHandler` — приймають лише `name`, `prefix` (null = пропуск через `array_filter`).
- HTTP:
  - `StoreProjectRequest`, `UpdateProjectRequest` — без правила для `status`.
  - `ProjectsController::store` / `update` — пробрасують лише `name`, `prefix` у command.
  - `ProjectResource` — повертає `id`, `name`, `prefix`, `created_by`, `updated_by`, `created_at`, `updated_at`.
  - `ProjectOverviewResource` — повертає `id`, `name`, `prefix`.
- Factory `ProjectModelFactory` — повертає лише `name`, `prefix`.

Frontend:

- `entities/project/types/project.types.ts`:
  - `IProject` — без `status`.
  - `ProjectOverviewDto` = `Pick<IProject, 'id' | 'name' | 'prefix'>`.
  - `ICreateProjectInput`, `IUpdateProjectInput` — без `status`.
- `entities/project/api/project.api.ts` — типи приходять із types, тіло запиту не змінюється структурно.
- `widgets/projects/upsert-dialog`:
  - `ProjectUpsertDialog.vue` — лише поле `name`.
  - `use.project-upsert-dialog.ts` — стан `name`, `submit` посилає `{ name }` у `create`/`update`.
- `pages/projects/list/ProjectsPage.vue` — `DataTable` колонки: `Prefix`, `Project Name`, `Created`. Без `Status`. `sortFieldDefs` без `status`. `filterSidebar` без `status`.
- `pages/projects/details/tabs/ProjectOverviewPage.vue` — `DisplayField` для `Name`, `Prefix`, `Created By`, `Updated By`, `Created At`, `Updated At`. Без `Status`.
- `widgets/projects/project-icon/ui/ProjectIcon.vue` — `Avatar` із жорстко прописаним кольором `!bg-blue-600 !text-white`. Розміри — через `PROJECT_ICON_SIZE_MAP`. Не знає про статус.

Існуючий патерн (Task):

- `entities/task/types/task-status.types.ts` — `TaskStatusValue` (union), `TaskStatusMetadata { label, value, color: HexColor }`, `TaskStatusMetadataMap`.
- `entities/task/config/task-status.config.ts` — `TaskStatusMap: TaskStatusMetadataMap` із `label`, `value`, `color` (hex). Функція `taskStatusOptions()`.
- `widgets/tasks/metadata/ui/TaskStatusTag.vue` — PrimeVue `Tag`, варіанти `light` / `dark`, опційна іконка, рендерить `meta.label` зі стилями на основі `color`. Fallback на сірий (`#6b7280`) і `'None'` коли статусу немає.

## Decisions Locked In

- **Без нової міграції.** Додати колонку у наявну `2026_05_30_113928_create_projects_table.php`.
- **NOT NULL з default `'draft'`** на рівні БД.
- **Тип колонки**: `string` з фіксованою довжиною (наприклад `->string('status', 20)`), без MySQL `enum` (контроль типу — на рівні app через `ProjectStatus` enum cast). Це збігається з підходом для `prefix`.
- **Default у domain layer**: `ProjectStatus::DRAFT`.
- **Update semantics**: якщо клієнт не передав `status` — поле не змінюється (поточна логіка `array_filter` у `UpdateProjectHandler` зберігається).
- **Виправити друкарську помилку** в enum: `ACTIOVE` → `ACTIVE`.
- **Frontend default UI**: при відкритті create-діалогу обирається `DRAFT`. У update-діалозі — поточне значення проєкту.
- **`ProjectStatusTag`** — новий widget за патерном `TaskStatusTag`. Реалізує тег із текстовою назвою статусу (`Draft`, `Active`, ...) та кольором із мапи. Підтримує варіанти `light` / `dark`. Використовується у списку проєктів та overview-вкладці.
- **`ProjectIcon`** — отримує необов'язковий prop `status?: ProjectStatusValue`. Колір тла беруться з тієї ж мапи кольорів, що і `ProjectStatusTag`. Якщо `status` не передано — поточний колір `!bg-blue-600` (default, без змін до існуючих місць використання).

## Open Questions

1. Чи потрібно показувати колонку `Status` у списку проєктів (`ProjectsPage`)? — пропоную **так**, після `Prefix`.
2. Чи потрібен sort/filter по `status` у списку проєктів? — пропоную **filter** (через select-options), **sort** не критичний.
3. Чи потрібно показувати `status` у `ProjectOverviewResource` / `ProjectOverviewDto` (lookup, breadcrumbs)? — пропоную **ні**, лише в основному `ProjectResource` / `IProject`, щоб не розширювати overview без потреби.
4. Який UI для статусу у списку — звичайний текст чи `Tag` із кольором per status? — пропоную `Tag` з мапою кольорів у конфізі (як патерн для `project-icon`).
5. Локалізація / лейбли статусів — використовувати безпосередньо назви enum-кейсів (`Draft`, `Active`, ...) чи завести мапу label'ів? — пропоную мапу `STATUS_LABELS` у `entities/project/config`.

Потребують підтвердження перед імплементацією.

## Scope

### Backend — Enum

- `app/Domains/Project/Enums/ProjectStatus.php`:
  - Перейменувати кейс `ACTIOVE` → `ACTIVE` (значення `'active'` залишити).

### Backend — Migration

- `database/migrations/2026_05_30_113928_create_projects_table.php`:
  - У `Schema::create('projects', ...)` додати після `prefix`:
    ```php
    $table->string('status', 20)->default(\App\Domains\Project\Enums\ProjectStatus::DRAFT->value);
    ```
  - Колонка `NOT NULL` (default Laravel behavior).
  - Нову міграцію **не створювати**.

### Backend — Model

- `app/Domains/Project/Models/ProjectModel.php`:
  - Додати `status` у `#[Fillable([...])]`.
  - У `casts(): array` додати `'status' => ProjectStatus::class`.
  - Додати `@property ProjectStatus $status` PHPDoc на класі (вимога PHPStan для enum casts — див. CLAUDE.md).
  - `toSearchableArray()` — додати `'status' => $this->status->value` (для Scout / пошуку).
  - `allowedFilters()` — додати фільтр по `status` (узгодити з `Open Questions #2`; пропоную `EnumFilter`/`TextFilter` залежно від наявних, перевірити `app/Libs/EloquentFilters/Filters/`).

### Backend — Factory

- `database/factories/ProjectModelFactory.php`:
  - У `definition()` додати `'status' => ProjectStatus::DRAFT`.

### Backend — Actions

- `CreateProjectCommand`:
  - Додати поле `public readonly ProjectStatus $status = ProjectStatus::DRAFT`.
- `CreateProjectHandler`:
  - У `ProjectModel::create([...])` додати `'status' => $command->status`.
- `UpdateProjectCommand`:
  - Додати поле `public readonly ?ProjectStatus $status = null`.
- `UpdateProjectHandler`:
  - У масив для `array_filter` додати `'status' => $command->status`.

### Backend — HTTP

- `StoreProjectRequest`:
  - Додати правило:
    ```php
    'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
    ```
- `UpdateProjectRequest`:
  - Додати правило:
    ```php
    'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
    ```
- `ProjectsController::store`:
  - Прокинути `status: ProjectStatus::from($request->validated('status'))` у command, з fallback на `ProjectStatus::DRAFT` коли поле не прийшло.
- `ProjectsController::update`:
  - Прокинути `status` у command (null коли не прийшло).
- `ProjectResource`:
  - Додати `'status' => $this->status->value`.
- `ProjectOverviewResource`:
  - Не змінювати (див. Open Questions #3).

### Backend — Validation

- `./vendor/bin/pint` і `./vendor/bin/phpstan analyse` проходять.
- Запустити релевантні project-тести (якщо існують у `tests/`).

### Frontend — entities/project (типи + конфіг статусів)

За патерном `entities/task` (`task-status.types.ts`, `task-status.config.ts`).

- Новий файл `entities/project/types/project-status.types.ts`:
  ```ts
  import type { HexColor } from '@/shared/types'

  export type ProjectStatusValue =
      | 'draft'
      | 'active'
      | 'inactive'
      | 'archived'
      | 'completed'
      | 'on_hold'
      | 'cancelled'

  export type ProjectStatusMetadata = {
      label: string
      value: ProjectStatusValue
      color: HexColor
  }

  export type ProjectStatusMetadataMap = Record<ProjectStatusValue, ProjectStatusMetadata>
  ```
- Реекспорт у `entities/project/types/index.ts`.
- Новий файл `entities/project/config/project-status.config.ts`:
  ```ts
  import type { ProjectStatusMetadata, ProjectStatusMetadataMap } from '../types/project-status.types'

  export const ProjectStatusMap: ProjectStatusMetadataMap = {
      draft:      { label: 'Draft',      value: 'draft',      color: '#6b7280' },
      active:     { label: 'Active',     value: 'active',     color: '#3b82f6' },
      inactive:   { label: 'Inactive',   value: 'inactive',   color: '#9ca3af' },
      archived:   { label: 'Archived',   value: 'archived',   color: '#475569' },
      completed:  { label: 'Completed',  value: 'completed',  color: '#22c55e' },
      on_hold:    { label: 'On Hold',    value: 'on_hold',    color: '#f59e0b' },
      cancelled:  { label: 'Cancelled',  value: 'cancelled',  color: '#ef4444' },
  }

  export function projectStatusOptions(): ProjectStatusMetadata[] {
      return Object.values(ProjectStatusMap)
  }
  ```
  Конкретні `color`-значення підлягають погодженню з дизайном; вище — пропозиція.
- Реекспорт у `entities/project/config/index.ts`.
- `entities/project/types/project.types.ts`:
  - У `IProject` додати `status: ProjectStatusValue`.
  - У `ICreateProjectInput` додати `status?: ProjectStatusValue`.
  - У `IUpdateProjectInput` додати `status?: ProjectStatusValue`.
  - `ProjectOverviewDto` — без змін.

### Frontend — ProjectStatusTag widget

Новий widget, дзеркальний до `TaskStatusTag`.

- Створити `resources/js/widgets/projects/status-tag/`:
  - `ui/ProjectStatusTag.vue`:
    ```vue
    <script setup lang="ts">
    import { computed } from 'vue'
    import Tag from 'primevue/tag'
    import { Icon } from '@iconify/vue'
    import type { ProjectStatusValue } from '@/entities/project/types'
    import { ProjectStatusMap } from '@/entities/project/config'

    const props = withDefaults(
        defineProps<{
            status: ProjectStatusValue | null | undefined
            variant?: 'light' | 'dark'
            showIcon?: boolean
        }>(),
        { variant: 'dark', showIcon: false }
    )

    const meta = computed(() =>
        props.status ? (ProjectStatusMap[props.status] ?? null) : null
    )

    const styles = computed(() => {
        if (!meta.value) {
            return props.variant === 'dark'
                ? { backgroundColor: '#6b7280', color: '#ffffff' }
                : { backgroundColor: '#6b728020', color: '#6b7280' }
        }
        const color = meta.value.color
        return props.variant === 'dark'
            ? { backgroundColor: color, color: '#ffffff' }
            : { backgroundColor: `${color}20`, color }
    })
    </script>

    <template>
        <Tag :style="styles" title="Status">
            <Icon v-if="showIcon" icon="hugeicons:status" />
            {{ meta?.label ?? 'None' }}
        </Tag>
    </template>
    ```
  - `index.ts` — `export { default as ProjectStatusTag } from './ui/ProjectStatusTag.vue'`.

### Frontend — ProjectIcon (колір за статусом)

`widgets/projects/project-icon/ui/ProjectIcon.vue`:

- Додати **необов'язковий** prop `status?: ProjectStatusValue`.
- Обчислити стиль тла на основі статусу через `ProjectStatusMap[status].color`.
- Якщо `status` не передано — поведінка не змінюється: тло `!bg-blue-600`, текст `!text-white` (поточний default зберігається для всіх існуючих місць використання).
- Реалізація: замінити жорстко прописаний `!bg-blue-600` на обчислюваний `:style="{ backgroundColor: ... }"` тільки коли є `status`; інакше залишити поточний клас.

Приклад:

```vue
<script setup lang="ts">
import { computed } from 'vue'
import Avatar from 'primevue/avatar'
import type { AvatarProps } from 'primevue/avatar'
import type { ComponentSize } from '@/shared/types'
import type { ProjectStatusValue } from '@/entities/project/types'
import { ProjectStatusMap } from '@/entities/project/config'
import { PROJECT_ICON_SIZE_MAP } from '../project-icon.config'

const props = withDefaults(
    defineProps<{
        prefix: string
        size?: ComponentSize
        shape?: AvatarProps['shape']
        status?: ProjectStatusValue
    }>(),
    { size: 'medium', shape: 'square' }
)

const sizeClasses = computed(() => PROJECT_ICON_SIZE_MAP[props.size])

const statusStyle = computed(() => {
    if (!props.status) return undefined
    const color = ProjectStatusMap[props.status]?.color
    return color ? { backgroundColor: color } : undefined
})

const rootClass = computed(() => [
    '!text-white !font-semibold',
    sizeClasses.value.root,
    !props.status && '!bg-blue-600',
])
</script>

<template>
    <Avatar
        :label="prefix"
        :shape="shape"
        :pt="{
            root: { class: rootClass, style: statusStyle },
            label: { class: sizeClasses.label },
        }"
    />
</template>
```

Існуючі виклики `ProjectIcon` (без `status`) залишаться візуально незмінними. Місця виклику оновлювати в межах цієї задачі **не плануємо**.

### Frontend — Upsert Dialog

- `widgets/projects/upsert-dialog/composables/use.project-upsert-dialog.ts`:
  - Додати `const status = ref<ProjectStatusValue>('draft')`.
  - В `open(project?)`:
    - create: `status.value = 'draft'`.
    - update: `status.value = project.status`.
  - У `submit`:
    - create: `{ name: name.value, status: status.value }`.
    - update: `{ name: name.value, status: status.value }`.
  - Повернути `status` із composable.
- `widgets/projects/upsert-dialog/ui/ProjectUpsertDialog.vue`:
  - Додати prop/emit `status: ProjectStatusValue` (аналогічно `name`).
  - Додати PrimeVue `Select` з `projectStatusOptions()`, `optionLabel="label"`, `optionValue="value"`, `required`. Лейбл "Status".
  - Підв'язати до `validationErrors.status`.

### Frontend — Projects List

- `pages/projects/list/ProjectsPage.vue` (за підтвердженням Open Questions #1, #2):
  - Додати `<Column field="status" header="Status">` із `ProjectStatusTag :status="data.status"`.
  - У `filterSidebar` додати поле `status` як select-фільтр з опціями `projectStatusOptions()` — узгодити з API фільтрів проєкту.

### Frontend — Project Overview Tab

- `pages/projects/details/tabs/ProjectOverviewPage.vue`:
  - Додати `<DisplayField label="Status">` зі слотом, який містить `<ProjectStatusTag :status="project.status" />`.

### Frontend — Validation

- `npm run format`, `npm run lint`, `npm run types:check` проходять.

## Out of Scope

- Робочі переходи між статусами (state machine, заборонені переходи) — окрема задача.
- Авторизація / policy на зміну статусу (хто може переводити проєкт у `ARCHIVED` тощо) — окрема задача.
- Каскадні правила (наприклад, "при `ARCHIVED` сховати всі активні таски") — окрема задача.
- Зміна `ProjectOverviewResource` / `ProjectOverviewDto` (lookup-поля проєкту).
- Активне використання статусу у бізнес-логіці інших доменів (Task, TaskList, Attachment).

## Acceptance Criteria

- Колонка `status` присутня у таблиці `projects`, NOT NULL, default `'draft'`. Нової міграції не створено.
- Enum `ProjectStatus` має кейс `ACTIVE` (замість `ACTIOVE`).
- API:
  - `POST /projects` без `status` створює проєкт зі статусом `draft`.
  - `POST /projects` з валідним `status` — створює з переданим значенням.
  - `POST /projects` з невалідним `status` — повертає 422 з помилкою валідації.
  - `PATCH /projects/{id}` без `status` не змінює статус.
  - `PATCH /projects/{id}` з валідним `status` — оновлює.
  - `GET /projects/{id}` повертає поле `status` у відповіді.
- Frontend:
  - У діалозі створення проєкту поле Status за замовчуванням `Draft`, можна обрати інше значення.
  - У діалозі редагування поточний статус підставлений, користувач може змінити і зберегти.
  - `ProjectStatusTag` рендерить лейбл і колір згідно `ProjectStatusMap`. Без статусу — fallback `'None'` на сірому.
  - У списку проєктів видно колонку Status (за підтвердження Open Q#1) — рендериться через `ProjectStatusTag`.
  - У overview-вкладці деталей проєкту видно поле Status — рендериться через `ProjectStatusTag`.
  - `ProjectIcon` без `status` виглядає як раніше (синій). З переданим `status` — фарбується кольором із `ProjectStatusMap`. Жодне з існуючих місць виклику не зламано.
- `./vendor/bin/pint`, `./vendor/bin/phpstan analyse`, `npm run format`, `npm run lint`, `npm run types:check` — без помилок.
