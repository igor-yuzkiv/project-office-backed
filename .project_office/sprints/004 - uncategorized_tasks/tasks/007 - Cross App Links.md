---
type: task
status: draft
---

# 007 - Cross App Links: Project From Task Details

## Goal

Зробити Project клікабельним посиланням у двох точках сторінки Task Details: у header'і (`TaskDetailsPage`) та на вкладці Details (`TaskOverviewPage`). Клік веде на `project-details`. Стиль посилання повторює патерн із `TasksTable` (`text-primary-500 hover:underline`).

## Context

Поточні дисплеї cross-entity references на Task Details:

- `resources/js/pages/tasks/details/TaskDetailsPage.vue:62-67` — header показує `ProjectIcon` + `task.project.name` як статичний `<span>`; `task.task_list.name` теж статичний.
- `resources/js/pages/tasks/details/tabs/TaskOverviewPage.vue:24-25` — Project поле як текст `${prefix} - ${name}`; Task List поле як текст `name`.

В інших місцях cross-link patterns уже існують:

- `resources/js/widgets/tasks/tasks-table/ui/TasksTable.vue:54-65` — колонка Project лінкує на `project-details` через RouterLink з класами `text-primary-500 block truncate hover:underline`.
- `resources/js/widgets/projects/upsert-dialog`, navigation sidebar, header action button — також використовують RouterLink.

Маршрут призначення вже існує: `project-details` (`resources/js/app/router/index.ts:28`).

## Decisions Locked In

- **Task List link**: не додавати. Cтільки `task-list-details` сторінки немає, і ми не вводимо filter-based deep link у межах цієї задачі. Task List лишається текстом.
- **User links** (`created_by`, `updated_by`): не додавати. `user-details` сторінки немає.
- **Стиль посилання**: inline `<RouterLink>` з Tailwind класами `text-primary-500 hover:underline`. Не вводити shared `EntityLink` компонент чи per-entity link wrapper'и.
- **Header link area**: лише `<span>` з `task.project.name` стає посиланням. `ProjectIcon` лишається статичним візуальним елементом.
- **Scope місць**: лише `TaskOverviewPage` і `TaskDetailsPage` header. Інші сторінки (Project Overview, Project Tasks tab, інші audit-fields) — поза задачею.

## Scope

**Frontend — TaskDetailsPage header:**

- У `resources/js/pages/tasks/details/TaskDetailsPage.vue` (приблизно рядки 62–65) замінити `<span class="text-sm text-surface-500">{{ task.project.name }}</span>` на `<RouterLink>`:
  - `to={ name: 'project-details', params: { id: task.project_id } }`;
  - класи `text-sm text-primary-500 hover:underline`;
  - текст — `task.project.name`.
- `ProjectIcon` лишити поза посиланням (статичний).
- `task.task_list` рядок (`<DisplayField v-if="task.task_list" label="Task List" :value="task.task_list.name" inline />`) не змінювати.

**Frontend — TaskOverviewPage Details tab:**

- У `resources/js/pages/tasks/details/tabs/TaskOverviewPage.vue` рядок 24 — замінити `<DisplayField label="Project" :value="task.project ? \`${task.project.prefix} - ${task.project.name}\` : null" />` на варіант зі слотом:

```vue
<DisplayField label="Project">
    <RouterLink
        v-if="task.project"
        :to="{ name: 'project-details', params: { id: task.project_id } }"
        class="text-primary-500 hover:underline"
    >
        {{ task.project.prefix }} - {{ task.project.name }}
    </RouterLink>
</DisplayField>
```

- Поле Task List (рядок 25) лишити як є (текст).
- Якщо `DisplayField` ще не підтримує slot fallback за відсутності value — використати `v-if="task.project"` як у прикладі.

**Frontend — імпорт RouterLink:**

- У обох файлах додати `import { RouterLink } from 'vue-router'` якщо ще не імпортовано (TaskDetailsPage уже не імпортує, TaskOverviewPage уже не імпортує). У Vue 3 + Vue Router 4 `RouterLink` доступний глобально, але явний імпорт — узгоджений патерн (див. `TasksTable`).

## Out Of Scope

- Task List посилання (немає destination'у).
- User (`created_by`/`updated_by`) посилання.
- Інші сторінки: `ProjectOverviewPage`, `ProjectTasksPage`, `ProjectAttachmentsPage`, `ProjectTaskListsPage` (після 006), `TaskAttachmentsPage` (після 001), `TaskDescriptionPage`, `TaskCommentsPage`, `TaskEditPage`.
- Shared `EntityLink` / per-entity wrapper'и (`ProjectLink`, тощо).
- Зміни в `TasksTable`, `ProjectsPage`, `TasksPage` та інших таблицях.
- Зміна стилю наявних посилань (наприклад, у `TasksTable` колонці Project — лишати як є).
- Deep-link filtering (наприклад, Task List → Tasks tab з префільтром).
- Backend зміни.

## Expected Behavior

- На сторінці Task Details у header'і назва проекту (`task.project.name`) відображається стилем `text-primary-500 hover:underline`, при кліку відбувається перехід на `project-details` поточного проекту.
- На вкладці Task Details → Details поле Project показує `{prefix} - {name}` як посилання у тому ж стилі, перехід на `project-details`.
- `ProjectIcon` у header'і лишається візуально таким самим і не клікабельний.
- Поля Task List на обох сторінках лишаються текстовими.
- Hover стан показує підкреслення для тексту посилання.
- Поведінка решти сторінок не змінюється.

## Technical Notes

- Дотримуватись існуючого Tailwind класу `text-primary-500 hover:underline` (без `block truncate`, які потрібні у TasksTable через width constraints колонки).
- Не вводити нові компоненти / композіційні абстракції.
- Не додавати aria/title атрибутів понад те, що `RouterLink` дає за замовчуванням, якщо немає prior pattern у проекті.
- Перевірити, що hover/visited стани не конфліктують з глобальними стилями.
- Frontend validation: `npm run format`, `npm run lint`, `npm run types:check`.
- Не встановлювати нові packages.

## Acceptance Criteria

- [ ] `TaskDetailsPage.vue` header показує `task.project.name` як `<RouterLink>` на `project-details` із класами `text-sm text-primary-500 hover:underline`.
- [ ] `ProjectIcon` у header'і лишається не клікабельним.
- [ ] `task.task_list.name` у header'і лишається текстом.
- [ ] `TaskOverviewPage.vue` поле Project показує `{prefix} - {name}` як `<RouterLink>` на `project-details` із класами `text-primary-500 hover:underline`.
- [ ] `TaskOverviewPage.vue` поле Task List лишається текстом.
- [ ] Інші поля і сторінки не змінено.
- [ ] Frontend validation: format + lint + types:check проходять.

## Open Questions

- Точна Tailwind клас-комбінація стилю посилання (чи додавати `font-medium` для емфази у header'і) — узгодити під час review якщо візуал виглядає недостатньо помітно. За замовчуванням використовується точна комбінація з `TasksTable`.
- Чи потрібен trailing arrow icon (`pi pi-external-link` або подібний) біля посилання — за замовчуванням ні; додавати лише за окремим запитом.

## Notes For Developer Agent

- Не розширювати scope на інші сторінки і не вводити shared `EntityLink` компонент — це може бути окремою задачею у майбутньому, коли кількість cross-link точок зросте.
- Не додавати link на Task List, навіть якщо здається логічним — destination не визначений.
- Не змінювати `DisplayField` API поза використанням default slot, якщо він уже підтримується. Якщо не підтримується — невелике мінімальне розширення допускається в межах цієї задачі.
- Залишити інші audit fields (`created_by`, `updated_by`, `created_at`, `updated_at`) без змін.
