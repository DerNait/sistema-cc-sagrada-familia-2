# 🚀 Pasos para levantar Laravel en Docker (DEV)

Este documento explica cómo montar el proyecto Laravel en un nuevo equipo usando **Docker** y **docker-compose**.

---

## 📦 1. Levantar contenedores (build incluido)
Este comando levanta todos los servicios (`app`, `web`, `db`, `vite`, etc.) definidos en `docker-compose.yml` y `docker-compose.override.yml`.
```bash
docker compose -p finaltest -f docker-compose.yml -f docker-compose.override.yml up -d --build
```

---

## 📂 2. Crear directorios y permisos de Laravel
Laravel requiere ciertas carpetas (`storage` y `bootstrap/cache`) para almacenar logs, vistas compiladas y sesiones.  
Con este comando nos aseguramos de que existan y tengan los permisos correctos.
```bash
docker compose -p finaltest exec app sh -lc 'mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache && chown -R www:www storage bootstrap/cache && chmod -R u+rwX,g+rwX storage bootstrap/cache'
```

---

## 📥 3. Instalar dependencias PHP (Composer)
Instalamos todas las dependencias de PHP definidas en `composer.json`.
```bash
docker compose -p finaltest exec app sh -lc 'composer install'
```

---

## 🔑 4. Generar APP_KEY
Laravel necesita una clave de aplicación (`APP_KEY`).  
Si ya existe, este comando no falla gracias a `|| true`.
```bash
docker compose -p finaltest exec app sh -lc 'php artisan key:generate || true'
```

---

## 🧹 5. Limpiar y regenerar caches
Se limpian y regeneran las caches de configuración, rutas y vistas para evitar errores de caché corrupta.
```bash
docker compose -p finaltest exec app sh -lc 'php artisan config:clear && php artisan cache:clear && php artisan view:clear || true && php artisan route:clear'
```

---

## 🗄️ 6. Migraciones de base de datos
Finalmente aplicamos las migraciones a la base de datos configurada en `.env`.
```bash
docker compose -p finaltest exec app sh -lc 'php artisan migrate'
```

---

✅ Con estos pasos, el proyecto debería quedar listo para usar en cualquier equipo.
