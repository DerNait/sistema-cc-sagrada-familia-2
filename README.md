# Gestion para Centro Cultural Sagrada Familia 2

Proyecto Laravel + Vue 3 + Vite + PostgreSQL en Docker 🐳

---

## ✨ Tecnologías utilizadas

- Laravel 12
- Vue 3 con Vite
- PostgreSQL 14
- Docker + Docker Compose

---

## 👀 Requisitos

- [Docker](https://www.docker.com/products/docker-desktop)
- [Docker Compose](https://docs.docker.com/compose/)
- (Opcional) Make o Bash para comandos rápidos

---

## 📂 Clonación del repositorio

```bash
git clone https://github.com/tu-usuario/tu-repo.git
cd tu-repo
```

---

## 📁 Preparación de archivos de entorno

Copiá los archivos de ejemplo:

```bash
cp .env.example .env
cp docker-compose.yml.example docker-compose.yml
cp docker-compose.override.yml.example docker-compose.override.yml
```

Luego podés editar el `.env` para ajustar valores como:
- `APP_NAME`
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

---

## ⚡ Levantar el proyecto

```bash
docker compose up --build
```

Esto va a:
- Instalar dependencias de PHP y JS
- Levantar el servidor Laravel en `http://localhost:8080`
- Levantar Vite (frontend Vue) en `http://localhost:5173`
- Levantar PostgreSQL en `localhost:5434`

---

## 🚪 Acceder al contenedor de la app (opcional)

```bash
docker compose exec app bash
```

Una vez dentro, podés correr comandos de Laravel:

```bash
php artisan migrate       # Ejecutar migraciones
php artisan db:seed       # Poblar la base de datos
php artisan tinker        # Probar cosas en consola
```

---

## 💡 Tips adicionales

### 👔 Si no se genera la key de Laravel:
```bash
php artisan key:generate
```

### 🌐 Entradas por defecto
- Laravel: http://localhost:8080
- Vite: http://localhost:5173
- PostgreSQL: localhost:5434

---


## 📊 Estado actual

- [x] Backend Laravel funcionando
- [x] Frontend Vue con Vite funcionando
- [x] Base de datos PostgreSQL funcionando
- [x] Todo corre en contenedores aislados

---