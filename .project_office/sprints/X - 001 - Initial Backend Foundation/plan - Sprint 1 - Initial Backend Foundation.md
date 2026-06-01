# Sprint 1: Initial Backend Foundation

## Scope

- Реалізувати базові сутності та їх структуру в БД.
- Підготувати моделі, міграції, factories та JSON resources.
- Реалізувати CRUD API для `Project`, `Task List` і `Task`.
- Виконувати create/update/delete operations через actions/handlers.
- Підготувати універсальний API для завантаження attachments.
- Звірити frontend pagination types зі стандартною Laravel pagination response.
- Підготувати frontend entity types і API request layer для сутностей sprint.

## Правила реалізації

### Controllers

- Розміщувати controllers у `app/Http/Controllers`.
- Для кожної сутності використовувати окремий controller.
- Для controllers сутностей використовувати додаткову папку.

Приклад:

```text
app/Http/Controllers/Projects/ProjectsController.php
```

### Domain Actions

- CRUD operations мають виконуватись через action/handler.
- Це потрібно, щоб у майбутньому було простіше підключити feed, notifications та інші side effects.

Очікувана структура:

```text
Domain/
└── Entity/
    ├── Actions/
    │   └── CreateEntity/
    │       ├── CreateEntityHandler.php
    │       ├── CreateEntityCommand.php
    │       └── CreateEntityDTO.php
    ├── Queries/
    ├── Models/
    ├── Events/
    ├── Jobs/
    ├── Enums/
    └── ValueObjects/
```

## Сутності

Під підготовкою сутності мається на увазі створення:

- model;
- migration;
- factory;
- JSON resource;
- frontend type у `resources/js/entities/entity_type/types/entity_type.types.ts`.

## Project

Сутність вже існує.

Потрібно додати:

- JSON resource;
- CRUD API, якщо ще не реалізовано повністю.

## Task List

Поля:

- `id`: ULID;
- `project_id`;
- `name`;
- auditable columns.

## Task

Поля:

- `id`: ULID;
- `project_id`;
- `task_list_id`: nullable;
- `key`: `project_prefix` + next project task sequence number;
- `sequence_number`: increment у межах project;
- `name`;
- `description`: nullable long text;
- `priority`: integer у БД, enum у коді;
- `status`: string у БД, enum у коді;
- auditable columns.

Правила:

- `key` створюється один раз і не змінюється після створення Task;
- `sequence_number` рахується в межах project;
- `task_list_id` може бути пустим.

Стартові значення `priority`:

- `low`: `10`;
- `medium`: `50`;
- `high`: `100`.

Стартові значення `status`:

- `open`;
- `in_progress`;
- `completed`;
- `closed`.

## API

Під реалізацією API мається на увазі:

Backend:

- controller;
- route;
- form request, якщо потрібна validation layer;
- extra JSON resource, якщо потрібен окремий response shape.

Frontend:

- entity extra types, якщо потрібні;
- API request у `resources/js/entities/entity_type/api/`.

## CRUD Contract

Під CRUD для сутності маються на увазі такі endpoints:

- `index`: Laravel paginated response;
- `show`: record details;
- `create`: `CreateCommand`, `CreateHandler`, record details response;
- `update`: `UpdateCommand`, `UpdateHandler`, record details response;
- `delete`: `DeleteHandler`, status response.

Потрібно уточнити:

- response status/body для delete;
- чи create/update мають використовувати DTO або command-only підхід.

## Pagination

Backend має використовувати стандартну Laravel pagination structure.

Frontend types потрібно звірити з фактичним Laravel response:

```text
resources/js/shared/types/pagination.types.ts
```

## Attachments API

### `POST /api/attachments`

Універсальний endpoint для завантаження attachments під сутність або без прив'язки до сутності.

Request params:

- `file`: required;
- `entity_type`: nullable string;
- `entity_id`: nullable string;
- `role`: nullable, приклади: `task_description`, `task_documents`, `task_attachments`.

Поточна модель attachments вже використовує `entity_type` і `entity_id` для зв'язку із сутністю.

Рекомендація щодо `entity_type`: використовувати стабільні module/entity aliases, наприклад `projects`, `tasks`, які можна валідувати через enum або whitelist.

Потрібно уточнити:

- які типи файлів дозволені;
- максимальний розмір файлу;
- response shape після завантаження;
- чи `entity_type` має бути enum/whitelist;
- чи потрібен rename `EntityRef` на `ModuleRef` у межах цієї задачі.

## Список задач для декомпозиції

- Prepare Project JSON Resource
- Implement Project CRUD API
- Create Task List Entity
- Implement Task List CRUD API
- Create Task Entity
- Implement Task CRUD API
- Implement Attachments Upload API
- Verify Frontend Pagination Types
- Define Frontend Entity Types
- Add Frontend API Request Layer
- Add Factories And Basic Test Coverage
