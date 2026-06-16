---
type: sprint
status: in_progress
---

# Sprint 000 - Comments

## Goal

V1 коментарів на сторінці задачі: автентифікований user може писати markdown-коментар, бачити список усіх коментарів задачі з пагінацією і власноруч редагувати/видаляти лише свої. Реалізація через нову polymorphic-сутність `Comment` що в перспективі легко розширюється на інші entity (Project тощо), але v1 експонує тільки під Task.

Референс UI: `.project_office/design/concept/task_detail_view.png` (нижня секція "Comments").

## Expected Outcome

- Новий backend domain `app/Domains/Comment/` із polymorphic `CommentModel` + Handler-actions Create/Update/Delete.
- REST endpoints для task-scoped index/store і id-scoped update/delete з Policy-based авторизацією.
- Новий FE `entities/comment/` (api / types / queries / mutations).
- Новий FE widget `widgets/comments/` із композером (`md-editor`) і списком (`MarkdownPreview`).
- Інтегрований таб "Comments" на сторінці задачі.
- Поточний Reverb / broadcast wiring НЕ чіпається.
- Майбутнє підключення Notifications робиться через додавання Laravel event у Handlers (нульова реструктуризація).

## Scope

- Backend: міграція `comments`, `CommentModel`, Domain Handlers (Create/Update/Delete), Policy, Controller, FormRequests, Resource, routes.
- Frontend: `entities/comment/`, `widgets/comments/`, інтеграція таба на сторінці задачі.
- Vue Query — стандартний паттерн (queryKey, invalidation).
- Markdown content через існуючі `md-editor` і `MarkdownPreview`.

## Out Of Scope

- Threads / replies.
- Realtime / WebSocket / Reverb (хоча wiring у проєкті є — НЕ чіпаємо).
- Notifications (push/email/in-app) і dispatch Laravel events у Handlers.
- Mentions `@user`.
- Реакції / likes.
- Attachments всередині коментаря.
- Search по коментарях.
- Role-badges автора в коментарі (як "Project Owner" у мокапі).
- Soft delete і audit/restore поведінка.
- Comments на інших entity, крім Task (модель готова, endpoints/UI — ні).
- Admin / project-owner override на edit/delete чужих коментарів.

## Tasks

### 001 - Comments MVP

Статус: todo

Імплементація end-to-end: backend domain + REST + Policy, frontend entity + widget + інтеграція таба. Розбити по саб-агентах згідно з task-документом.

## Dependencies

* Існуючі `shared/components/md-editor` і `MarkdownPreview` (вже є).
* `UserAvatar` widget (вже є).
* Vue Query (вже сконфігурований).
* Polymorphic relations у Laravel (стандартні).

## Risks

* Polymorphic morph map: якщо `commentable_type` зберігати як FQCN — refactor моделей надалі ламає БД. Краще явний morph alias (`taskModel.aliasMap` або `Relation::enforceMorphMap()`). Підтвердити в реалізації.
* `MarkdownPreview` має покривати XSS-санітайзацію (як для project description). Якщо ні — це окремий ризик для UGC.
* Перевірити чи `Task` модель уже має морф-relation pattern, інакше — додати трейт `HasComments` або relation на `TaskModel`.

## Open Questions

(всі критичні питання вирішені до старту реалізації; дрібні рішення — у Technical Notes task-файлу)

## Notes For Developer Agent

- Topology: 2 active components (Domain & API + UI), 2 deferred (Realtime, Notifications). Deferred не реалізовувати, але архітектурно не блокувати їх майбутню інтеграцію.
- Архітектурний hook під notifications = додавання `event(new CommentCreated(...))` у Handlers в майбутньому. Зараз events НЕ створюємо.
- Архітектурний hook під broadcasting = таж сама точка, через `ShouldBroadcast` на майбутньому event.
- Розбити task по саб-агентах (див. task-документ).
- Не міняти Reverb config, не торкатись поточних broadcast-channels.
