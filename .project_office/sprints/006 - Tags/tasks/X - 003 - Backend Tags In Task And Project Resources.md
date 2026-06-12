---
type: task
status: draft
---

# 003 - Backend Tags In Task And Project Resources

## Goal

Додати поле `tags` (перші 4 за порядком прив'язки) у Task і Project API resources. Розширити Create/Update handlers Task і Project для прийому `tag_ids` та `sync` прив'язок.

## Context

Frontend очікує бачити теги на сторінках Task і Project. У list/detail responses достатньо перших 4 — повний список доступний через окремий endpoint (task 004). Прив'язка тегів відбувається через `tag_ids` у тих самих handlers, що створюють/оновлюють сутність.

## Scope

- Додати у `TaskResource` поле `tags` — масив ресурсів `TagResource`, обмежений першими 4 за `taggables.created_at ASC`.
- Додати у `ProjectResource` те саме.
- Розширити Task Create і Update Handlers (і відповідні Commands / FormRequests):
  - приймати `tag_ids: string[]` (ULID існуючих тегів);
  - після створення/оновлення сутності — викликати `$task->tags()->sync($tagIds)`;
  - якщо `tag_ids` відсутній у payload (не переданий) — не змінювати прив'язки.
- Аналогічно для Project Create і Update Handlers.
- Додати валідацію у FormRequest:
  - `tag_ids` опціональне, array;
  - кожен елемент — `string` + `exists:tags,id`.
- Забезпечити eager loading тегів у list endpoints, де вже використовується `TaskResource` / `ProjectResource`, щоб уникнути N+1.

## Out Of Scope

- Endpoint для повного списку тегів сутності (окрема task 004).
- Створення нових тегів через цей flow (тільки sync на існуючих ID).
- Фільтрація за тегами (task 005).
- Frontend.

## Expected Behavior

Resource:

```json
{
    "id": "...",
    "name": "...",
    "tags": [
        {"id": "...", "name": "bug", "color": "#ff0000"},
        ...
    ]
}
```

- `tags` завжди присутнє, навіть якщо порожнє.
- Максимум 4 елементи. Порядок — порядок прив'язки.

Update / Create:

- Якщо передати `tag_ids: []` — усі прив'язки видаляються.
- Якщо `tag_ids` не передано взагалі — прив'язки не змінюються.
- Передача `tag_ids` з ID неіснуючого тега → `422`.

## Technical Notes

- Для обмеження 4 — використовувати relation з лімітом через subquery або сортувати + slice після eager load. Найпростіший варіант — relation `tagsLimited()` із `orderBy('taggables.created_at')->limit(4)` + eager load цього relation. У ресурсі повертати саме його.
- `sync` має повертатися тільки після успішного збереження сутності.
- Жодного автоматичного створення нових тегів — `sync` тільки з ID, інакше — помилка валідації.
- Узгодити з task 002 формат TagResource.

## Acceptance Criteria

- [ ] `TaskResource` має поле `tags` з максимумом 4 елементів, порядок — `taggables.created_at ASC`.
- [ ] `ProjectResource` має поле `tags` за тими ж правилами.
- [ ] Створення Task з `tag_ids` прив'язує всі передані теги.
- [ ] Оновлення Task з `tag_ids` синхронізує прив'язки (додає нові, видаляє відсутні).
- [ ] Те саме для Project.
- [ ] Відсутність `tag_ids` у payload не змінює прив'язки.
- [ ] `tag_ids: []` видаляє всі прив'язки.
- [ ] Невалідний ID у `tag_ids` повертає `422`.
- [ ] Немає N+1 при listing Tasks/Projects.
- [ ] Pint і PHPStan проходять без нових помилок.
- [ ] Додано targeted tests на sync поведінку.

## Open Questions

- N/A

## Notes For Developer Agent

- Не змінювати інші поля existing Resources.
- Не вводити окремий endpoint для оновлення тільки тегів — сценарій покривається існуючими Task/Project Update.
- Sync прив'язок має бути в межах однієї транзакції з основним апдейтом сутності.
