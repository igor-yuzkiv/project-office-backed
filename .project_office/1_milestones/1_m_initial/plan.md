
# Scope

- реалізувати базу для мінімального набору сутносту
- моделі
- crud actions and commands/dto
- апі для завантаження атачменів під сутніст, поки планується тільк для збереження скрішотів чи ще чогось для описів задач чи проектів 
- кожна круд операцію має виконуватись через action/handler для того щоб в майбуньому думаю можна буде простіше підключити feed і сповіщення

# Enities

## Task List

- id: ulid
- key: project_prefix + (last seq number + 1)
- sequence_number: increment
- project_id
- name
- AuditableColumns

## Task

- id: ulid
- key: project_prefix + (last seq number + 1)
- sequence_number: increment
- name
- description: nullable, long text
- priority: в бд integer але треба enum
- status: в бд string, але треба enum поки без кастомних


## Tags

- id, 
- name, 
- color
- scope, null = доступно для всього, task, project, etc
- поліморфні звязки

# Api


# POST: api/attachments

в межах цієї задачі виконати ренейм EntityRef на ModuleRef та поля в цьому value object та поля в моделі Attachments

- file: required
- 