# Dockerfile для запуска Laravel-приложения на Render
FROM php:8.2-cli

# Системные зависимости и расширения PHP
RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite zip \
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

# Создаём файл базы SQLite и права на запись
RUN mkdir -p database && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database

# Render передаёт порт через переменную $PORT
ENV PORT=10000
EXPOSE 10000

# Стартовый скрипт: миграции + сидер + запуск сервера
CMD php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan serve --host 0.0.0.0 --port ${PORT}
