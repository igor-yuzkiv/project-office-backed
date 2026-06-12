---
type: task
status: draft
---

# 005 - Backend Task And Project Tag Filtering

## Goal

Додати фільтр за тегами на list endpoints Task і Project з OR semantics.

## Context

Sprint 004 додав узагальнений filtering механізм через `app/Libs/EloquentFilters`. Frontend Task/Project list pages дозволяє вибирати кілька тегів — backend має повертати сутності, що мають хоча б один із вибраних тегів.

## Scope

- Створити filter class у `app/Libs/EloquentFilters` (або іншій існуючій папці filter-ів, узгодженій з sprint 004) для фільтрації за тегами.
- Зареєструвати filter у allowed filters Task і Project моделей.
- Реалізувати OR semantics:
  - `whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))`.
- Додати індекс на `taggables.tag_id`, якщо ще не доданий у task 001 (узгодити).
- Додати targeted backend test, що перевіряє OR semantics.

## Out Of Scope

- AND semantics або перемикач.
- Виведення тегів у фільтр-сайдбарі (frontend).
- Глобальний tags listing endpoint для frontend filter UI (буде у frontend tasks).
- Фільтрація інших сутностей за тегами.

## Expected Behavior

Filter payload:

```json
{
    "filter": "tags",
    "value": ["01H...id1", "01H...id2"]
}
```

- Якщо `value` порожній — фільтр ігнорується (стандартна поведінка filter infrastructure).
- Якщо передано один або кілька ID — повертати сутності, де є хоча б один із вибраних тегів.
- Якщо ID невалідний (не існує) — повертати порожній результат для цього критерію, без помилки.

## Technical Notes

- Назву filter key узгодити з конвенціями sprint 004 (`tags`).
- Generic filter не підходить — створити dedicated filter class із власною логікою `whereHas`.
- Не вводити в payload `matchMode`. Тільки OR.

## Acceptance Criteria

- [ ] Існує filter class для тегів, зареєстрований у allowed filters Task і Project моделей.
- [ ] Фільтр `tags` з масивом ID повертає сутності з хоча б одним із вибраних тегів.
- [ ] Порожнє `value` не змінює запит.
- [ ] Невалідні ID не викликають помилку.
- [ ] Pint і PHPStan проходять без нових помилок.
- [ ] Додано targeted test.

## Open Questions

- N/A

## Notes For Developer Agent

- Не дублювати логіку — base filter abstractions sprint 004 мають покривати реєстрацію та resolver.
- Не намагатися додати tags filter до Scout search builder, якщо це вимагає окремої інтеграції — обмежитися Eloquent builder.
