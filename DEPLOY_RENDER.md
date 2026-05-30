# Деплой на Render с PostgreSQL

## Часть 1. Залить проект на GitHub

В корне проекта (D:\projects\lab-queue-app) выполнить по очереди:

```
git init
git add .
git commit -m "Курсовой: очередь на сдачу лаб"
git branch -M main
git remote add origin https://github.com/ВАШ_ЛОГИН/lab-queue.git
git push -u origin main
```

Репозиторий на github.com создать заранее, ПУСТОЙ (без README/gitignore).

## Часть 2. Создать базу PostgreSQL на Render

1. render.com -> New -> Postgres.
2. Name: lab-queue-db. Region: тот же, что и сайт (Frankfurt). Plan: Free.
3. Create Database. Дождаться статуса Available.
4. На странице базы найти "Internal Database URL" — пригодится дальше.

## Часть 3. Создать веб-сервис

1. New -> Web Service -> выбрать репозиторий lab-queue.
2. Render увидит Dockerfile (Runtime: Docker). Region: Frankfurt. Plan: Free.

## Часть 4. Переменные окружения веб-сервиса

В разделе Environment добавить:

| Ключ            | Значение                                              |
|-----------------|-------------------------------------------------------|
| APP_KEY         | локально: php artisan key:generate --show (с base64:) |
| APP_ENV         | production                                            |
| APP_DEBUG       | false                                                 |
| DB_CONNECTION   | pgsql                                                 |
| DATABASE_URL    | Internal Database URL из Части 2                      |

Laravel умеет читать DATABASE_URL целиком — отдельные DB_HOST/DB_PORT
указывать не нужно.

3. Create Web Service. Render соберёт образ, прогонит миграции с сидером,
   поднимет сайт на https://lab-queue-xxxx.onrender.com

## Вход старосты на боевом сайте

/login -> starosta@example.com / password (СМЕНИТЬ ПАРОЛЬ!)

## Обновление сайта

Любой git push в main автоматически пересобирает сервис.

## Заметки про бесплатный план

- Сервис засыпает после 15 мин простоя, первый запрос ~30 сек.
- PostgreSQL постоянная — данные НЕ пропадают (в отличие от SQLite).
- Бесплатная база Render живёт ограниченный срок (~90 дней),
  потом нужно создать новую. Для курсового это не проблема.
