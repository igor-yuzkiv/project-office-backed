## Tags

- Create Tags entity.
- Implement Tags CRUD API.
- Add polymorphic relations for attaching tags to supported entities.
- Tags мають підтримувати entity-level прив'язку через polymorphic relations.
- Exact API contracts, validation rules і task decomposition потрібно уточнити перед реалізацією.

# Recent Projects

- додати hadnler/query GetProjectHandler який відповідає за відображення деталей по проекту, поки вератиме тільки модель але в персепективі можна DTO якщо буде якась специфічна вибірка з релейшенами чи агрегацією
- і додати сервіс типу TackRecentProjectsService чи RecentProjectService який буде збергати в бд запис про відвідування прокту юзером
- він має зберігати історію усіх відвідувань
- але треба якось подумати за ротацю даних щоб вони не накопичувались занадто багато
- і на фронті показувати в меню недавно відвідані проекти
- думаю це можна зробити через окрмий vue-query і інвалідувати ключи коли користувач заходить в проект що автоматично тригрне рефеч і оновить список
- 
# URL State

- Реалізувати URL-based filters and sort.


# Custom View
- типу як в срм щоб користувачі створвали відфільтровані вю 