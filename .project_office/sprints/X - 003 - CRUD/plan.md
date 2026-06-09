# Sprint 3: Frontend CRUD Initial

## Scope

- Ініціювати frontend CRUD для `Project`.
- Ініціювати frontend CRUD для `Task List`.
- Ініціювати frontend CRUD для `Task`.

## Goals

- Підготувати початкову frontend реалізацію сценаріїв створення, перегляду, редагування та видалення сутностей.
- Використати існуючі backend API та frontend інфраструктуру.
- Зберегти поточну архітектуру, naming conventions та структуру модулів.
- Закласти основу для подальшого розширення CRUD flows без повного фінального polish у межах цього sprint.

## Entities

### Project

Потрібно декомпозувати frontend CRUD flow.

### Task List

Потрібно декомпозувати frontend CRUD flow.

### Task

Потрібно декомпозувати frontend CRUD flow.

## Draft Task List

- Initialize Project Frontend CRUD
- Initialize Task List Frontend CRUD
- Initialize Task Frontend CRUD
- Refactor Backend API To Flat Resources And Search Endpoints
- Create TasksPage With Global Tasks List
- Create Project Details Page
- Create Task Dialog
- Task Edit Page

## Questions

- Які саме UI states потрібні для create/update/delete flows?
- Який очікуваний UX для delete confirmation?
- Чи потрібні окремі сторінки details, чи достатньо modal/drawer flows?
- Які validation errors мають відображатись на frontend?
- Чи потрібні filters/search/sorting у межах цього sprint?
- Який фінальний контракт плоских backend endpoints для `Task List` і `Task` після відмови від вкладених routes?

### Resolved For Task 006

- `Task` create dialog має містити тільки `name` і `project`.
- `priority` для `Task` create flow має бути nullable і за замовчуванням `null`.
- Project lookup у Task create dialog поки реалізується локально через PrimeVue `AutoComplete`, без shared lookup component.
- Після створення task користувача потрібно перенаправити на порожню `TaskDetailsPage`.
- Project autocomplete має показувати початкові suggestions і підтримувати debounce через VueUse.

### Planning Draft For Task 007

- `Task Details And Edit Pages` планується як дві окремі сторінки з однаковою структурою:
  - `TaskDetailsPage` read-only;
  - `TaskEditPage` editable.
- Placeholders і routes для details/edit сторінок уже існують; 007 має наповнити їх реальним UI.
- Мета 007 на цьому етапі: реалізувати мінімальні details/edit pages з title/identity block, `Task Information`, description section і placeholder tabs.
- Save/Cancel для 007 мають бути в app shell header actions.
- Create flow redirect на `task-details` сумісний із 007, бо `TaskDetailsPage` тепер входить у scope.
