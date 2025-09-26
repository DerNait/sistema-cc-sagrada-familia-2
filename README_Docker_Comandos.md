# 🚀 Comandos útiles para DevOps con Docker y Laravel

Este README resume los comandos que hemos usado durante los despliegues y mantenimientos.

---

## 🔧 Comandos básicos

### Levantar y construir contenedores
```bash
docker compose -p arkend -f docker-compose.yml -f docker-compose.override.yml up -d --build
```

### Ejecutar comandos dentro del contenedor `app`
```bash
docker compose -p arkend exec app sh -lc 'php artisan migrate'
docker compose -p arkend exec app sh -lc 'php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear'
docker compose -p arkend exec app sh -lc 'php artisan key:generate --force'
```

### Entrar al contenedor (bash/sh)
```bash
docker compose -p arkend exec app sh
docker compose -p arkend exec web sh
```

### Ver logs
```bash
# Logs del web (Nginx)
docker compose -p arkend logs -f web

# Logs de la app (Laravel)
docker compose -p arkend exec app sh -lc 'tail -n 200 storage/logs/laravel.log'
```

---

## 📦 Vite build (Frontend)

### 1) Compilar assets en el host (Node dentro de contenedor temporal)
```bash
docker run --rm -v "$PWD":/workspace -w /workspace node:18-bullseye sh -lc "npm ci --legacy-peer-deps && npm run build"
```

### 2) Copiar `public/` al volumen compartido con Nginx
```bash
docker run --rm -v "$PWD/public":/src:ro -v arkend_app_public:/dst busybox sh -lc "cd /src && tar cf - . | (cd /dst && tar xvf -)"
```

### 3) Copiar `build/` al contenedor de Laravel (para manifest.json)
```bash
APP_CID=$(docker ps -qf "name=l_app")
docker cp ./public/build "$APP_CID":/var/www/html/public
```

### 4) Limpiar cachés de Laravel
```bash
docker compose -p arkend exec app sh -lc "php artisan view:clear && php artisan config:clear"
```

### 5) Reiniciar Nginx
```bash
docker compose -p arkend restart web
```

---

## 🗂️ Volúmenes y depuración

### Inspeccionar red y contenedores
```bash
docker ps
docker inspect l_app
docker inspect l_web
```

### Revisar archivos dentro de contenedor
```bash
docker compose -p arkend exec web sh -lc 'ls -la /var/www/html/public/build'
docker compose -p arkend exec app sh -lc 'ls -la public/build && head -n 2 public/build/manifest.json'
```

---

✅ Con este flujo puedes:  
1. Construir contenedores.  
2. Revisar logs.  
3. Generar claves y limpiar cachés.  
4. Compilar assets con Vite.  
5. Actualizar el volumen compartido con Nginx.  
