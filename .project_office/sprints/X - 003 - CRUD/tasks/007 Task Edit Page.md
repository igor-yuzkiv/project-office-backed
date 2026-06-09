---
type: task
status: draft
planning_status: implementation_draft
---

# 007 - Task Details And Edit Pages

## Planning Status

Це implementation draft після первинного UI planning і відповідей автора проєкту на відкриті питання.

Документ фіксує основні рішення щодо двох сторінок:

* read-only `TaskDetailsPage`;
* editable `TaskEditPage`.

Сторінки мають однакову структурну основу. Різниця тільки в режимі: details page показує всі поля read-only, edit page дозволяє редагувати погоджений набір полів.

## Goal

Реалізувати task details page і task edit page з ідентичною структурою: користувач може переглядати task у read-only режимі, перейти до редагування, змінити погоджені поля task, а нижні секції поки залишаються placeholders.

## Context

У task 006 створюється базовий перехід на task page після створення task.

Task 007 має реалізувати дві окремі route-level сторінки, а не комбінований details/edit mode в одному route:

* `TaskDetailsPage` для read-only перегляду;
* `TaskEditPage` для редагування.

Поточний route/page context уже існує в коді:

* `resources/js/pages/tasks/TaskDetailsPage.vue`;
* route name: `task-details`;
* path: `/tasks/:id`;
* `resources/js/pages/tasks/TaskEditPage.vue`;
* route name: `task-edit`;
* path: `/tasks/:id/edit`.

Placeholders для `TaskDetailsPage` і `TaskEditPage`, а також routes для них уже налаштовані. У межах 007 потрібно наповнити існуючі сторінки реальним read-only/edit UI.

Дизайн-орієнтири:

* edit page concept: `.project_office/design/concept/edit_task_page.png`;
* read-only task view concept for future context only: `.project_office/design/concept/view_task_details_page.png`.

Read-only task view тепер входить у scope 007.

## Scope

Що входить у задачу:

* Наповнити існуючу route-level `TaskDetailsPage`.
* Наповнити існуючу route-level `TaskEditPage`.
* Обидві сторінки мають використовувати однакову структуру:
  * title/identity block;
  * `Task Information`;
  * `Description`;
  * placeholder tabs.
* Завантажити task з backend перед показом editable form.
* Завантажити task з backend перед показом read-only details page.
* Після завантаження task склонувати дані в окремий локальний state `formData`.
* Не прив'язувати header title напряму до live-edited `formData.name`, щоб назва в header не змінювалась під час редагування до save.
* Оновити page/header title для details і edit routes у форматі:
  * `Task Number | Task Name`;
  * приклад: `AT-124 | Redesign enterprise onboarding flow`.
* Не змінювати загальну структуру app shell header у межах цієї задачі.
* Використати app shell header actions для page-level actions:
  * primary `Save`;
  * secondary `Cancel`.
* У тілі сторінки першим елементом показати editable task title:
  * виглядає як page heading;
  * користувач може поставити курсор і змінити назву.
* На details page title має бути read-only.
* Додати секцію `Task Information`.
* У `Task Information` показати editable поля:
  * `task_list_id`;
  * `status`;
  * `priority`.
* На details page всі поля в `Task Information` показати read-only.
* Для read-only fields використати `resources/js/shared/components/display/ui/DisplayField.vue`.
* `name` редагувати через editable title block.
* `description` редагувати через окрему markdown editor секцію.
* На details page `description` показати read-only через `MdPreview` з `md-editor-v3`.
* Всі інші task поля показувати read-only або не показувати, якщо вони не потрібні для першої версії.
* `project` показувати тільки read-only.
* Підтримати `task_list` lookup у `Task Information`.
* Додати окрему секцію `Description`.
* Для `Description` використати markdown editor.
* Додати нижні placeholder tabs за аналогією з `ProjectDetailsPage`.
* Tabs можуть бути placeholder-only; точний список tabs не критичний на цьому етапі.
* Використати дизайн-концепт `edit_task_page.png` як основний UI reference, без pixel-perfect вимоги.

## Out Of Scope

Що не входить у задачу:

* Реалізація comments.
* Реалізація attachments.
* Реалізація documentation.
* Реалізація activity feed.
* Linked tasks.
* Time tracking.
* Assignee/reporter/sprint/due date data model, якщо цих полів немає в поточній task model/API.
* Зміни app shell header structure.
* Нові global header action components.
* Pixel-perfect implementation of `edit_task_page.png`.

## Proposed Page Structure

### 1. Existing App Shell

Використати існуючий application shell без зміни структури header/sidebar.

Header actions з `resources/js/app/shell/ui/header` використовуються без зміни структури header.

Очікуваний вплив на header:

* route/page title має показувати task number і task name після завантаження task.
* details page має мати action для переходу в edit page, якщо це вже підтримується existing header action pattern.
* edit page header actions мають містити:
  * `Save`;
  * `Cancel`.

### 2. Editable Task Title

Перший блок у тілі сторінки:

* task number/key як compact badge;
* optional status/priority badges або controls;
* editable task title, стилізований як заголовок.

На edit page назва task редагується в `formData.name`.

На details page назва task показується read-only.

Header title має залишатися прив'язаним до fetched/original task data, а не до live-edited value.

### 3. Task Information Section

Секція з metadata fields для task.

Editable fields на edit page:

* `task_list_id`;
* `status`;
* `priority`.

Read-only fields на details page:

* всі поля.

Read-only field на edit page:

* `project`.

Примітки:

* `name` редагується тільки у title block.
* `description` редагується тільки у markdown editor section.
* `project` не можна змінювати на edit page.
* `task_list_id` має використовувати lookup/select flow.
* `priority` може бути nullable після task 006.
* Для read-only rendering використовувати `DisplayField`.

### 4. Description Section

Окрема секція під `Task Information`.

Edit page вимоги:

* markdown editor;
* орієнтир: `md-editor-v3`;
* без окремого preview panel;
* вигляд має бути ближчим до editor/WYSIWYG writing area.

Для `md-editor-v3` використовувати editor mode з вимкненим preview panel. Якщо toolbar за замовчуванням дозволяє preview toggle і це суперечить UX, прибрати preview-related controls через available toolbar options.

Details page вимоги:

* description read-only;
* використовувати `MdPreview` з `md-editor-v3`;
* якщо description порожній, показати fallback markdown text на кшталт `_No description available._`.

### 5. Placeholder Tabs

Під description додати tabs за аналогією з:

* `resources/js/pages/projects/ProjectDetailsPage.vue`.

Можливі labels:

* `Comments`;
* `Attachments`;
* `Activity`;
* `Documentation`.

На цьому етапі tabs є placeholder-only. Їхній точний список не критичний.

## Expected Behavior

Користувач відкриває `/tasks/:id`.

Сторінка завантажує task з backend і показує read-only task details page. Всі поля, включно з description, показуються read-only. Description рендериться через `MdPreview`.

Користувач відкриває `/tasks/:id/edit`.

Сторінка завантажує task з backend. Після завантаження task data копіюється в локальний `formData`.

Header/page title на details і edit routes показує task number і task name зі fetched task data, наприклад `AT-124 | Redesign enterprise onboarding flow`.

Користувач може редагувати task title у першому блоці сторінки, змінювати `task_list_id`, `status`, `priority` у `Task Information`, редагувати description у markdown editor і бачити placeholder tabs нижче.

До моменту save header title не має змінюватись від live редагування назви.

Save action у app shell header зберігає зміни. Cancel action у app shell header скасовує редагування або повертає користувача з edit flow без збереження.

## Technical Notes

* Перед реалізацією перевірити актуальні `ITask`, `IUpdateTaskInput`, `fetchTaskRequest`, `updateTaskRequest`.
* Якщо query/mutation для single task або update task ще відсутні, створити їх за existing entity patterns.
* Single task query має підтримувати include для `project` і `task_list`, якщо ці relations потрібні для read-only/details display.
* Для `task_list` lookup використати той самий UX/pattern, що project lookup у task create dialog.
* Поточний frontend `task_list` layer має API requests, але не має query/search composables на рівні `resources/js/entities/task_list/queries`; якщо вони відсутні на момент реалізації, створити їх за existing entity patterns.
* Не додавати новий shared lookup abstraction без окремого погодження.
* Використати PrimeVue components і Tailwind за існуючими patterns.
* Tabs placeholder можна реалізувати за pattern `ProjectDetailsPage`.
* Save/Cancel actions мають бути в app shell header actions.
* Якщо Cancel реалізується як navigation action, використати передбачуваний route назад до task details/list flow.
* Current local code note: `useTaskCreateDialog` currently redirects to `task-details` after create. Після включення `TaskDetailsPage` у 007 цей redirect є сумісним з поточним планом.
* Приклад `MdPreview` з іншого проєкту використовує `useAppThemeStore`, але в поточному frontend такого store не знайдено. Для MVP потрібно або використати наявний theme mechanism, або залишити стабільну light/default theme конфігурацію без додавання окремого theme store.
* Для `MdPreview` використати налаштування за прикладом:
  * `language="en-US"`;
  * `codeFoldable=false`;
  * `previewTheme="github"`;
  * `codeTheme="github"`.

## Acceptance Criteria Draft

Це попередній список, не фінальний.

* [ ] Існуючий placeholder `TaskDetailsPage` замінений read-only task UI.
* [ ] Існуючий placeholder `TaskEditPage` замінений editable task UI.
* [ ] Details page завантажує task за route param `id`.
* [ ] Edit page завантажує task за route param `id`.
* [ ] Details page і edit page мають ідентичну базову структуру.
* [ ] Після завантаження task дані копіюються в `formData`.
* [ ] Header/page title на обох сторінках показує `Task Number | Task Name`.
* [ ] Header title не змінюється під час live редагування `formData.name`.
* [ ] App shell header actions містять `Save` і `Cancel`.
* [ ] У тілі сторінки є editable task title.
* [ ] На details page task title read-only.
* [ ] Є секція `Task Information`.
* [ ] `Task Information` містить editable поля `task_list_id`, `status`, `priority`.
* [ ] На details page всі поля у `Task Information` read-only.
* [ ] Для read-only task fields використано `DisplayField`.
* [ ] `project` показаний read-only.
* [ ] `name` редагується через editable title block.
* [ ] `task_list` редагується через lookup/select flow.
* [ ] Є секція `Description` з markdown editor.
* [ ] Markdown editor не має split preview panel.
* [ ] `description` редагується через markdown editor.
* [ ] На details page `description` read-only і рендериться через `MdPreview`.
* [ ] Empty description на details page має fallback text.
* [ ] Save action викликає update task flow для `name`, `description`, `task_list_id`, `priority`, `status`.
* [ ] Cancel action виходить з edit flow або скидає зміни згідно з реалізованим navigation pattern.
* [ ] Нижче description є placeholder tabs.
* [ ] Comments/Attachments/Activity/Documentation behavior не реалізований у межах цієї задачі.

## Resolved Decisions

* Save action має бути в app shell header actions.
* Cancel action має бути достатнім для першої версії і теж має бути в app shell header actions.
* `project` на edit page тільки read-only.
* Editable fields першої версії:
  * `task_list_id`;
  * `name`;
  * `description`;
  * `priority`;
  * `status`.
* `task_list` lookup має використовувати той самий UX/pattern, що project lookup.
* Для `md-editor-v3` використовувати editor mode без split preview panel.
* Details page входить у scope 007.
* Details page має ідентичну структуру з edit page, але всі поля read-only.
* Для read-only fields використовувати `DisplayField`.
* Для read-only description використовувати `MdPreview`.

## Notes For Developer Agent

Ця задача включає наповнення двох уже створених route-level сторінок, бо вони мають однакову структуру і відрізняються тільки режимом read-only/edit.

Не реалізовувати comments, attachments, documentation або activity behavior у межах цієї задачі.

`view_task_details_page.png` і `edit_task_page.png` є дизайн-орієнтирами, але pixel-perfect реалізація не потрібна.
