---
type: task
status: draft
---

# 009 - Rethink Tags Input

## Goal

Замінити поточну систему тегів (`TagInput` multilookup + `CreateTagDialog` + `ViewAllTagsDialog`) на єдиний компонент `ManageTagsDialog` з двома режимами (`edit` / `read-only`). Розширити `GET /api/tags` глобальним лічильником використання тега (`uses_count`).

## Context

Поточна реалізація (sprint 006) має три окремі компоненти для трьох сценаріїв: `TagInput` для редагування в формі, `CreateTagDialog` для створення нового тега, `ViewAllTagsDialog` для перегляду повного списку. UX роздрібнений, користувачу важко тримати загальну картину тегів сутності.

Design: `.project_office/design/concept/ManageTagsDialog.png` — єдиний dialog з двома станами, lifted state, нова поведінка створення тегів інлайн у search input.

Persistence logic призначення тегів **не змінюється**: створення нового тега йде через `POST /api/tags` одразу, призначення тегів сутності лишається частиною submit основної форми.

## Scope

### Frontend — новий компонент `ManageTagsDialog`

Розташування — узгоджено з widget-конвенцією (за зразком `widgets/projects/upsert-dialog/ui/ProjectUpsertDialog.vue`): `widgets/tags/manage-dialog/ui/ManageTagsDialog.vue`.

Props:
- `visible: boolean` (через `defineModel`).
- `modelValue: string[]` — масив selected tag IDs (через `defineModel`).
- `mode: 'edit' | 'read-only'`.
- `record: { type: 'task' | 'project', id: string, label?: string }` — контекст для header (`Task AT-124 · Redesign...`).

**Edit mode layout:**
- Header: іконка + title `Manage Tags` + subtitle із record context + close `×`.
- **Selected Tags** секція: чіпи з `×` (видалення з селекції), лічильник `N selected` праворуч.
- **Search / Create input**: один input `Search existing tags or create a new one...`.
  - Коли є збіги — праворуч іконка `↵ Enter` (підказка).
  - Коли збігів немає — іконка зникає, з'являються:
    - кругла кнопка-прев'ю кольору (default — рандомний HEX), клік відкриває `vue3-colorpicker` palette;
    - кнопка `Create`.
  - `Enter` або клік `Create` → `POST /api/tags` → новий тег додається до Selected + в All Tags.
- **All Tags** секція:
  - лейбл `ALL TAGS` + total count;
  - кнопка sort `Sort by name` (default ASC, тогл ASC ↔ DESC);
  - segmented filter `All` / `Selected` / `Available` (default `All`);
  - список рядків: color dot + name + `<uses_count> uses` (праворуч) + toggle:
    - якщо selected — синій checkbox;
    - якщо не selected — `+` icon-button.
  - Toggle переключає selection локально (без API call).
  - Scroll усередині секції, dialog має фіксовану максимальну висоту.
- Footer:
  - ліворуч статус `N tags selected · Changes apply to this {type} only`;
  - праворуч `Cancel` + `Save Changes`.
- `Save Changes` → emit `update:modelValue` з новим масивом ID, закрити dialog.
- `Cancel` / close `×` → закрити без emit.

**Read-only mode layout:**
- Header як в edit mode.
- Selected Tags секція: чіпи без `×`, без лічильника.
- Без search / Create input.
- Без All Tags, без filter, без sort.
- Footer відсутній (тільки close `×` у header).
- Жодних мутацій.

### Frontend — `TagList` зміни

- `visibleLimit = 4` (узгоджено з sprint 006).
- На **edit**-сторінках і edit-попапах: `+N` — non-clickable індикатор. Поряд — pencil button → відкриває `ManageTagsDialog` у `edit`.
- На **detail**-сторінках: pencil прихований. `+N` clickable → відкриває `ManageTagsDialog` у `read-only`.
- `+N` показується тільки при `total > visibleLimit`.
- Pencil на edit-сторінках присутній завжди (навіть якщо `tags.length === 0`), щоб юзер міг додати теги.

### Frontend — видалення

Видалити після того як усі точки використання переключені:
- `TagInput` (multilookup).
- `CreateTagDialog`.
- `ViewAllTagsDialog`.

### Frontend — інтеграція

- `EditTaskPage`:
  - прибрати `TagInput` із форми;
  - додати `TagList` із pencil-кнопкою → `ManageTagsDialog` (`edit`, `record = { type: 'task', id }`);
  - `tag_ids` тримається у формі як і раніше; submit Task update працює без змін.
- `ProjectUpsertDialog`:
  - те саме; `tag_ids` передається у Create / Update Project payload як зараз.
- Task details page, Project details page:
  - використовують `TagList` із clickable `+N` → `ManageTagsDialog` (`read-only`).

### Backend — `uses_count` у `GET /api/tags`

- Додати поле `uses_count: int` (глобальний) у `TagResource`:
  - сума всіх рядків `taggables`, де `tag_id = tag.id` (Task + Project разом).
- Реалізувати через `withCount('taggables')` або агрегатний subquery у `TagsController@index` (uniform для search і без `search`).

## Out Of Scope

- Зміна persistence flow призначення тегів (лишається через submit основної форми).
- Новий dedicated endpoint для tag sync (`PATCH /api/tasks/{id}/tags` тощо).
- Per-record-type `uses_count` (тільки глобальний).
- AND/OR перемикач у фільтрах списків Task/Project.
- Bulk operations над тегами.
- Розширення на інші сутності.

## Expected Behavior

### Сценарій: редагування тегів задачі

1. На `EditTaskPage` юзер бачить `TagList` із призначеними тегами + pencil.
2. Click pencil → `ManageTagsDialog` у `edit` mode з поточними `tag_ids`.
3. Юзер міняє вибір toggle-ами (checkboxes / +), видаляє через `×` в Selected.
4. Юзер вводить у search `payment` — збігів нема → з'являється Create + colored circle.
5. Click circle → palette → вибирає колір.
6. Click Create → `POST /api/tags` → новий тег додається в Selected + у All Tags.
7. Click `Save Changes` → dialog закривається, `tag_ids` у формі оновлюється.
8. Click Save на формі `EditTaskPage` → Task update із новими `tag_ids` → backend sync (як зараз).

### Сценарій: перегляд тегів на task details

1. На task details `TagList` показує перші 4 теги + `+15`.
2. Click `+15` → `ManageTagsDialog` у `read-only` mode з усіма призначеними тегами.
3. Close `×` → закриття. Жодних мутацій.

### Сценарій: створення тега в порожньому пошуку

1. У edit mode юзер вводить `release-2026` — збігів нема.
2. UI показує Create + colored circle (рандомний HEX як default).
3. (Опц.) Click circle → colorpicker → інший HEX.
4. Click Create → `POST /api/tags` → тег з'являється у Selected і All Tags.
5. Search очищається, юзер може шукати наступний.

## Technical Notes

- Один компонент `ManageTagsDialog`, внутрішня логіка перемикається через `mode` prop.
- Локальна копія selection у dialog, оригінал не мутується до `Save Changes`. `Cancel` відкидає зміни.
- Search — використати existing `useTagsSearch` із `entities/tag` із debounce ~300ms (узгодити з вже наявною реалізацією, не дублювати).
- Filter `All` / `Selected` / `Available` — клієнтська фільтрація поверх результатів search, не окремий API запит.
- Sort — клієнтський, default ASC.
- `vue3-colorpicker` — вже встановлений; інтегрувати **тільки** в інлайн no-results стейті, без окремого dialog wrapper.
- Color circle — round button з inline `background-color`, тригерить popover з palette. Не блокувати focus у search input.
- All Tags scroll — окрема скрол-зона всередині dialog з фіксованою максимальною висотою.
- `TagResource` має повертати `uses_count: int` стабільно (також для existing flows, де `uses_count` не використовується — це обчислюване число, додатковий cost минулого SELECT, не критичний на MVP-обсягах).
- `withCount('taggables')` — найпростіший Eloquent варіант. Якщо не підходить через polymorphic — subquery.
- Перенесення `tag_ids` у parent form — через `defineModel` + явний `update:modelValue` на `Save Changes`. Без global store, без provide/inject.

## Acceptance Criteria

- [ ] Створений `ManageTagsDialog` з props `visible`, `modelValue`, `mode`, `record`.
- [ ] Edit mode: працюють Selected секція, search/create input, All Tags список, filter `All/Selected/Available`, sort by name, footer `Cancel` / `Save Changes`.
- [ ] Edit mode: пошук без результатів показує Create button + color circle з `vue3-colorpicker`.
- [ ] Edit mode: Create робить `POST /api/tags` і авто-додає тег до Selected і All Tags.
- [ ] Edit mode: `Save Changes` емітить `update:modelValue` з новим масивом і закриває dialog.
- [ ] Edit mode: `Cancel` / close закриває без emit.
- [ ] Read-only mode: показано тільки Selected chips без `×`, без інших секцій, без footer.
- [ ] `TagList` на edit-сторінках/попапах має pencil button; `+N` non-clickable.
- [ ] `TagList` на detail-сторінках: pencil прихований; `+N` clickable і відкриває read-only dialog.
- [ ] `EditTaskPage` використовує `ManageTagsDialog` (`edit`), submit працює без зміни.
- [ ] `ProjectUpsertDialog` використовує `ManageTagsDialog` (`edit`), create і update flows працюють без зміни.
- [ ] Task details / Project details відкривають `ManageTagsDialog` (`read-only`) через `+N`.
- [ ] Видалені `TagInput`, `CreateTagDialog`, `ViewAllTagsDialog` і всі їх імпорти.
- [ ] `GET /api/tags` повертає `uses_count` (глобально по Task + Project) у `TagResource`.
- [ ] Pint, PHPStan проходять без нових помилок.
- [ ] `npm run format`, `npm run lint`, `npm run types:check` проходять.

## Open Questions

- Поведінка `Enter` у search input при наявності результатів: фокус на перший рядок чи нічого. Уточнити при реалізації; не блокер.
- Чи `+N` indicator на edit-сторінках варто прибрати взагалі (раз pencil вже є тригером), або залишити для інформації про обсяг. Рекомендована поведінка — залишити non-clickable, але це деталь UI; узгодити в процесі.
	- Відповідь: залишити для інформації про обсяг

## Notes For Developer Agent

- Не вводити dedicated PATCH endpoint для tag sync. Логіка збереження тегів до сутності лишається через submit основної форми (`UpdateTaskHandler`, `UpdateProjectHandler` із `tag_ids`).
- `vue3-colorpicker` лишається в залежностях, але використовується **тільки** в no-results стейті `ManageTagsDialog`.
- `uses_count` — глобальний, без context filtering. Не вводити параметри для зміни scope лічильника.
- Видалення старих компонентів виконати останнім кроком — після того, як усі точки використання переключені і працюють.
- Після видалення перевірити, що з `entities/tag` нічого зайвого не лишилось (експорти, типи), і що `index.ts` не реекспортує мертвий код.
