# Dockerfile для запуска Laravel-приложения на Render
FROM php:8.2-cli

# Системные зависимости и расширения PHP.
# libpq-dev нужен для PostgreSQL, libsqlite3-dev — для локального SQLite.
RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libsqlite3-dev libpq-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Сначала только манифесты — чтобы кешировался слой с зависимостями
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist || true

# Копируем остальной код
COPY . .
RUN composer install --no-dev --optimize-autoloader || true

# Права на запись для служебных папок
RUN chmod -R 775 storage bootstrap/cache

# Render передаёт порт через переменную $PORT
ENV PORT=10000
EXPOSE 10000

# Стартовый скрипт: кеш конфигурации + миграции + сидер + запуск сервера
CMD php artisan config:clear \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan serve --host 0.0.0.0 --port ${PORT}
