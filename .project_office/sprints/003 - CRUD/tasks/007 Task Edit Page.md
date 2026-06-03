---
type: task
status: draft
planning_status: structure_only
---

# 007 - Task Edit Page

## Planning Status

Це не фінальний task-документ для Developer Agent.

Поточна мета документа — зафіксувати структуру окремої task edit page, щоб на її основі згенерувати UI через Banani AI або схожий UI generation сервіс.

Implementation scope, API contract, exact fields, validation і acceptance criteria ще не фіналізовані.

## Goal

Спроєктувати структуру окремої сторінки редагування task, на якій користувач зможе змінювати основні поля задачі та опис.

## Context

У task 006 створюється route-level placeholder `TaskDetailsPage` і redirect на цю сторінку після створення task.

Task 007 має спроєктувати UI-структуру саме для редагування задачі. Це не має бути комбінована сторінка перегляду і редагування.

Дизайн-орієнтир:

* `.project_office/design/concept/view_task_details_page.png`

У референсі можна взяти загальну композицію: широка основна колонка, права metadata sidebar і верхні actions. Для MVP потрібно адаптувати цю структуру під edit workflow, не копіюючи read-only details behavior і всі mock fields з референсу.

## Proposed Page Structure

### 1. Page Shell

Сторінка використовує існуючий application shell і route:

* path: `/tasks/:id`;
* route name: `task-details`;
* page component: `resources/js/pages/tasks/TaskDetailsPage.vue`.

Внутрішній layout сторінки:

* full-height page content;
* top page header / breadcrumb area;
* main content area with two columns:
  * primary content column;
  * right editable metadata sidebar.

### 2. Top Header

Header має дати користувачу контекст поточної задачі та місце для основних actions.

Структурні елементи:

* breadcrumbs:
  * Project;
  * optional Task List;
  * Task key або Task name.
* task key chip, наприклад `TASK-124`.
* primary header action:
  * `Save`.
* secondary actions як placeholder only:
  * more menu;
  * copy link або similar action, якщо це буде потрібно пізніше.

Рішення, вже погоджене автором:

* save action має бути в header actions.

### 3. Primary Content Column

Основна колонка призначена для редагування task content.

Рекомендована структура:

* task title/name section;
* main form section;
* description editor section;
* optional lower activity/tabs area.

### 4. Task Title / Identity Section

Призначення: швидко показати, яку задачу редагує користувач.

Структурні елементи:

* task key badge;
* status badge або status select;
* priority badge або priority select;
* editable task name/title field.

Поля зі скріншота типу assignee, reporter, sprint, due date поки не переносити як вимоги, бо вони не підтверджені поточною task model.

### 5. Main Form Section

Це основна форма з полями задачі.

Поля-кандидати з поточної task model:

* `name`;
* `project`;
* `task_list`;
* `status`;
* `priority`.

Поля, які потрібно підтвердити окремо перед фіналізацією:

* assignee;
* reporter;
* sprint;
* due date;
* labels;
* time tracking;
* linked tasks.

Для UI generation можна показати ці непідтверджені поля як disabled/placeholder blocks тільки якщо вони потрібні для композиції макета. Не вважати їх implementation requirements.

### 6. Description Section

Окрема велика секція для опису задачі.

Вимога автора:

* для опису використати `md-editor-v3`;
* preview режим не потрібен;
* потрібен режим, який виглядає ближче до WYSIWYG/editor-only experience.

Структурні елементи:

* section label `Description`;
* markdown editor area;
* editor toolbar, якщо вона потрібна для WYSIWYG-like editing;
* без split preview panel.

Технічне питання для наступного планування:

* уточнити конкретний режим `md-editor-v3`, який найкраще відповідає WYSIWYG-like/editor-only UX.

### 7. Right Metadata Sidebar

Права колонка має бути компактною metadata form/sidebar для редагування або уточнення параметрів задачі.

Рекомендовані confirmed поля з поточної моделі:

* status;
* priority;
* project;
* task list;
* created at;
* updated at.

Можливі placeholder sections з дизайн-референсу, не підтверджені як implementation scope:

* assignee;
* reporter;
* due date;
* labels;
* linked tasks;
* time tracking.

Sidebar не має бути окремим read-only details view. Потрібно вирішити тільки те, які поля варто редагувати в sidebar, а які залишити в основній формі.

### 8. Lower Content Area

У дизайн-референсі нижче опису є tabs/activity area.

Можлива структура для UI generation:

* `Comments`;
* `Activity`;
* `Attachments`;
* `Documentation`.

На цьому етапі це тільки structural placeholder для макета. Реальна реалізація comments, attachments або documentation не входить у підтверджений scope 007.

## Out Of Scope For Current Structure Draft

Що не потрібно фіналізувати зараз:

* точний API update payload;
* validation rules;
* autosave або manual save behavior;
* comments implementation;
* attachments implementation;
* documentation implementation;
* labels implementation;
* time tracking implementation;
* linked tasks implementation;
* assignee/reporter/sprint/due date data model;
* delete task flow;
* exact responsive behavior;
* pixel-perfect UI.

## Banani AI UI Generation Notes

Ціль генерації: отримати layout/design для task edit page, не фінальну реалізацію.

Ключова композиція:

* task management app page;
* dense product UI, not marketing;
* top header with breadcrumbs and Save action;
* two-column details layout;
* primary editable task content on the left;
* compact editable metadata sidebar on the right;
* large description editor area;
* lower tabs/activity placeholder area;
* visual style consistent with existing MVP UI: PrimeVue/Tailwind-like, restrained, operational, not decorative.

Не генерувати:

* landing page;
* kanban board;
* dashboard widgets;
* decorative hero;
* full comments or attachments product flow.

## Final Banani AI Prompt

Design a task edit page for our SaaS task manager application, continuing the same visual direction from the existing designs already in this Banani session.

Use the existing task screen references and the current MVP UI direction as the base. This should feel like the same product: restrained, operational, dense, and built for repeated work. Do not create a marketing page, dashboard, kanban board, read-only details page, or decorative hero.

Design goal:

Create a polished task edit page. It should look like a real product screen where a user can change task fields, write a detailed markdown description, and save edits confidently.

Page structure:

* existing app shell with left navigation already present;
* top header with breadcrumbs such as `Atlas Platform / Sprint 14 / TASK-124`;
* header actions with a clear primary `Save` button;
* optional secondary actions such as `Copy link`, `Share`, and more menu;
* main content area split into two columns;
* wide left column for editable task content;
* compact right sidebar for editable task metadata;
* bottom area with lightweight tabs or placeholders.

Left primary column:

* task key badge;
* status badge or select;
* priority badge or select;
* large editable task title;
* compact row of high-level context fields;
* main editable form section;
* large `Description` section using an editor-only markdown editor area;
* no split preview panel for the markdown editor;
* optional lower tabs: `Comments`, `Activity`, `Attachments`, `Documentation`.

Right metadata sidebar:

Show a compact vertical metadata sidebar inspired by the existing task reference, but make it feel like part of the edit form, not a separate read-only details view. Include realistic field groups for design purposes.

Confirmed current-model fields:

* Status;
* Priority;
* Project;
* Task List;
* Created;
* Updated.

Future/planned design-only fields:

* Assignee;
* Reporter;
* Sprint;
* Due Date;
* Start Date;
* Estimate;
* Logged Time;
* Labels;
* Linked Tasks;
* Parent Task;
* Subtasks;
* Watchers;
* Last Activity;
* Created By;
* Updated By.

Main editable form fields:

Use a balanced form layout, not a huge settings page. Include fields that make the screen feel useful for future product planning:

* Task Name;
* Project;
* Task List;
* Status;
* Priority;
* Assignee;
* Reporter;
* Sprint;
* Due Date;
* Labels.

Description editor:

Create a large markdown editor section that feels close to a WYSIWYG writing experience. It should have an editor toolbar and a comfortable writing area, but no preview panel.

Lower content area:

Show lightweight tabs or placeholders for:

* Comments;
* Activity;
* Attachments;
* Documentation.

These lower sections should not dominate the page. They are secondary to the main task edit form and description editor.

Visual style:

* keep the UI quiet, utilitarian, and professional;
* use existing product styling from the current Banani session;
* use compact spacing suitable for a task management app;
* use badges, chips, selects, text inputs, date fields, and compact metadata rows;
* avoid oversized cards and decorative sections;
* avoid a one-note color palette;
* make the page responsive enough conceptually, but focus on desktop layout first.

Important constraint:

Some fields are included for design exploration and future planning only. Do not treat every visible field as an implementation requirement. The implementation scope will be finalized separately.

## Open Questions

* Які поля в main form мають бути confirmed implementation scope для першої версії?
* Чи `project` і `task_list` на edit page можна змінювати, чи вони мають бути заблокованими після створення task?
* Чи `status` і `priority` мають редагуватись у primary form, у right sidebar, чи в обох місцях?
* Чи нижні tabs потрібні у першій UI-структурі, чи краще залишити тільки description editor?
* Які поля мають редагуватись у right metadata sidebar, а які в основній формі?
* Чи потрібен sticky header/sidebar behavior?
* Який конкретний режим `md-editor-v3` використовувати для editor-only/WYSIWYG-like behavior?

## Notes For Developer Agent

Не використовувати цей документ як фінальну implementation task без додаткового уточнення open questions.

Цей документ описує тільки структуру сторінки для UI generation.
