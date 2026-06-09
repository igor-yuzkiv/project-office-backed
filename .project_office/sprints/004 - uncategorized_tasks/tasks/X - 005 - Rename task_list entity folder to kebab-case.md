---
type: task
status: draft
---

# 005 - Rename task_list Entity Folder To kebab-case

## Goal

Привести шлях frontend entity-модуля Task List до загального kebab-case naming, який використовується для всіх інших entity-папок (`attachment`, `project`, `task`, `user`) і widget'ів (`widgets/task-list/`). Перейменувати `resources/js/entities/task_list/` → `resources/js/entities/task-list/` і оновити всі імпорти.

## Context

Поточні entity-папки frontend:

- `resources/js/entities/attachment/`
- `resources/js/entities/project/`
- `resources/js/entities/task/`
- `resources/js/entities/task_list/` ← **snake_case, неконсистентно**
- `resources/js/entities/user/`

Widget та інші модулі вже використовують kebab-case (`widgets/task-list/`). Поточний snake_case у entities — єдиний виняток.

Існуючі споживачі (точок імпорту небагато):

- `resources/js/entities/task/types/task.types.ts` — `import type { ITaskList } from '@/entities/task_list/types'`
- `resources/js/widgets/task-list/lookup-field/ui/TaskListLookupField.vue` — `import type { ITaskList } from '@/entities/task_list/types'` + `import { useTaskListsSearchQuery } from '@/entities/task_list/queries'`
- `resources/js/pages/tasks/edit/TaskEditPage.vue` — `import type { ITaskList } from '@/entities/task_list/types'`

Backend і структура `task_lists` у БД не змінюються — лише frontend folder name.

## Scope

- Перейменувати папку `resources/js/entities/task_list/` → `resources/js/entities/task-list/`. Зберегти весь вміст без змін у файлах:
  - `api/`
  - `config/`
  - `queries/`
  - `types/`
- Оновити всі імпорти, що посилаються на `@/entities/task_list/...`, на `@/entities/task-list/...`. Точки оновлення (станом на момент написання):
  - `resources/js/entities/task/types/task.types.ts`
  - `resources/js/widgets/task-list/lookup-field/ui/TaskListLookupField.vue`
  - `resources/js/pages/tasks/edit/TaskEditPage.vue`
- Перевірити tsconfig path alias `@/` (`resources/js/`) — alias не вимагає змін, бо вказує на корінь, але переконатись що TS resolve працює.
- Жодних змін у вмісті файлів entity-модуля поза рядком frontmatter/коментарями (якщо є).
- Жодних backend змін.

## Out Of Scope

- Перейменування backend domain `app/Domains/TaskList/` (PHP namespace використовує PascalCase TaskList — це окрема структура з власним конвеншеном і вже консистентна).
- Перейменування БД таблиці `task_lists` (snake_case у БД — стандарт Laravel).
- Перейменування route name `project-details.task-lists` (уже kebab-case).
- Зміна вмісту файлів entity-модуля (типи, query keys, API функції).
- Будь-яке розширення функціональності (мутації, додаткові queries) — це виходить за межі цієї задачі.

## Expected Behavior

- Після перейменування `resources/js/entities/task-list/` існує, `resources/js/entities/task_list/` — ні.
- Всі імпорти у проекті використовують `@/entities/task-list/...`.
- `npm run types:check`, `npm run lint`, `npm run format:check` проходять без помилок.
- Збірка проекту через Vite не ламається.
- Існуюча функціональність (Tasks page, Task Lookup Field, Task Edit Page) працює без змін поведінки.

## Technical Notes

- Виконати rename через `git mv` для збереження git history.
- Перед commit пройтись `grep -rn "task_list/" resources/js` і `grep -rn "entities/task_list" resources/js` щоб переконатись що немає залишків.
- Не змінювати символьні імена (типи, функції) — лише шлях папки і відповідні імпорти.
- Не додавати додаткових файлів у `entities/task-list/`.
- Не torkатися backend і `routes/api.php` (там `task-lists` як URL сегмент — це окрема справа і вже kebab-case).

## Acceptance Criteria

- [ ] Папка `resources/js/entities/task-list/` існує з усім попереднім вмістом.
- [ ] Папка `resources/js/entities/task_list/` відсутня.
- [ ] Жоден файл проекту не імпортує з `@/entities/task_list/...`.
- [ ] `npm run lint`, `npm run format`, `npm run types:check` проходять.
- [ ] `npm run build` (опціонально, якщо швидкий) також не ламається.

## Open Questions

- Жодних — задача суто механічна.

## Notes For Developer Agent

- Це передумова до задачі `006 - Project Task Lists Tab`, яка використовує `entities/task-list/mutations/` і взаємодіє з `entities/task-list/api`/`queries`.
- Не розширювати scope — це чистий rename + import fix.
- Якщо під час grep знайдуться ще точки використання `task_list` як шляху імпорту — оновити їх також (список у Context є snapshot на момент написання, не повний garantia).
