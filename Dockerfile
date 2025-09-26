# ---- Stage 1: Composer (instala dependencias sin dev) ----
FROM composer:2 AS vendor
WORKDIR /app

# Copia archivos de Composer y resuelve dependencias primero (mejor cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

# Copia el resto del proyecto y revalida (por si hay scripts/autoload extra)
COPY . .
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

# ---- Stage 2: PHP-FPM con extensiones + OPcache ----
FROM php:8.2-fpm-alpine AS app
WORKDIR /var/www/html

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache libpq postgresql-dev libzip-dev busybox-suid icu-dev \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

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

COPY --from=vendor /app /var/www/html

RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www \
    && chown -R www:www /var/www/html \
    && mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache

# ⚠️ Pool FPM: workers como 'www'
RUN sed -ri 's/^user\s*=\s*.*/user = www/; s/^group\s*=\s*.*/group = www/' /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000
CMD ["php-fpm", "-F"]
