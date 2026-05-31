# Milestone 2: Auth And Tags

## Postponed from the past

- Implement Sanctum SPA Authentication.
- Add current user API through `GET /me`.
- Add login API through `POST /login`.
- Add logout API through `POST /logout`.
- Create Tags entity.
- Implement Tags CRUD API.
- Add polymorphic relations for attaching tags to supported entities.
- Auth має працювати через Laravel Sanctum SPA Authentication.
- `GET /me` має повертати current user через User JSON resource.


--- 

# Scope

- в межах цього мейлстоу потрібно релазувати механіз авторизаці
- та автентифікації апі ендпоінтів які наразі існують та підготувати її для майбутніх ендпоінтів
- для авторизації буде викоритсоуватись ## [SPA Authentication](https://laravel.com/docs/13.x/sanctum#spa-authentication)