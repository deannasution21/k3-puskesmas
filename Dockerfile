# syntax=docker/dockerfile:1

# ---------- Stage 1: build frontend (Vue) ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

# ---------- Stage 2: backend (Laravel + PHP) ----------
FROM php:8.3-cli AS backend

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY backend/ ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Hasil build Vue digabung ke public/spa (dilayani lewat routes/web.php fallback route)
COPY --from=frontend /app/dist ./public/spa

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["start.sh"]
