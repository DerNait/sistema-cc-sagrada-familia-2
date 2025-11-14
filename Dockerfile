# ---- Stage 1: Composer (instala dependencias sin dev, sin scripts) ----
FROM composer:2 AS vendor
WORKDIR /app

# Cache de composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts

# Copia resto del proyecto y revalida autoload/scripts si los hubiera
COPY . .
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

# ---- Stage 2: Vite build (Node) ----
FROM node:18-bullseye AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --legacy-peer-deps
COPY . .
RUN npm run build      # genera public/build/manifest.json + assets

# ---- Stage 3: PHP-FPM con extensiones + OPcache ----
FROM php:8.2-fpm-alpine AS app
WORKDIR /var/www/html

# Extensiones necesarias
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache libpq postgresql-dev libzip-dev busybox-suid icu-dev \
       libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath intl gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && apk add --no-cache libpng libjpeg-turbo freetype

# OPcache prod
RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=0"; \
  echo "opcache.jit=1255"; \
  echo "opcache.jit_buffer_size=64M"; \
  echo "opcache.validate_timestamps=0"; \
  echo "opcache.max_accelerated_files=40000"; \
  echo "opcache.memory_consumption=256"; \
  echo "opcache.interned_strings_buffer=32"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Copiamos código PHP (con vendor) y los assets de Vite
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

# Usuario no-root + permisos
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www \
    && chown -R www:www /var/www/html \
    && mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache

# Que FPM ejecute como 'www'
RUN sed -ri 's/^user\s*=\s*.*/user = www/; s/^group\s*=\s*.*/group = www/' /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000
CMD ["php-fpm", "-F"]
